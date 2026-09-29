<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Password\Auth\LoginRequest;
use App\Http\Requests\Api\Password\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\SmsMisrService;



class AuthController extends Controller
{
    /**
     * Handle a registration request.
     */
    public function register(RegisterRequest $request,SmsMisrService $verificationService): JsonResponse
    {
        $validated = $request->validated();

        $referredById = null;
        if (! empty($validated['referred_by_code'] ?? null)) {
            $referredBy = User::where('referral_code', $validated['referred_by_code'])->first();
            if ($referredBy) {
                $referredById = $referredBy->id;
                $referredBy->increment('points', setting('referral_points', 0));
            }
        }
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
            'is_verified' => false,
            'referred_by_id' => $referredById,
            
        ]);
        $user->assignRole('user');
        $smsResult = $verificationService->sendPhoneVerification($user);
        // $user->sendVerificationCode();
        // if ($validated['email']) {
        //     // Send verification email
        //     $user->sendEmailVerificationNotification();
        // } else {
        //     // Send verification code to phone
        // }
        event(new Registered($user));

        // Load roles and permissions for the response
        $user->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'message' => __('Registration successful. Please verify your account before logging in.'),
            'data' => [
                'user' => new UserResource($user),
            ],
        ], 201);
    }
    
    
    public function sendLoginCode(Request $request,SmsMisrService $verificationService){
        $data = $request->validate([
            'phone' => ['required', 'string', 'exists:users,phone'],
        ]);
        
        $user = User::where('phone', $data['phone'])->first();
        
        if (! $user) {
                return response()->json([
            'success' => false,
                'message' => __('User not found.'),
            ], 404);
        }
        
        $verificationService->sendLoginOTP($user);
        
        return response()->json([
            'success' => true,
            'message' => __('OTP code sent successfully.'),
        ]);
    }

    /**
     * Handle a login request to the application.
     */
    // public function login(LoginRequest $request): JsonResponse
    // {
    //     $request->authenticate();

    //     $user = Auth::user();

    //     // Load roles and permissions for the response
    //     $user->load('roles', 'permissions');

    //     // Revoke all existing tokens (optional - for single device login)
    //     // $user->tokens()->delete();

    //     // Create a new token
    //     $token = $user->createToken('auth-token')->plainTextToken;

    //     return response()->json([
    //         'success' => true,
    //         'message' => __('Login successful.'),
    //         'data' => [
    //             'user' => new UserResource($user),
    //             'token' => $token,
    //             'token_type' => 'Bearer',
    //         ],
    //     ]);
    // }
    
    public function login(LoginRequest $request): JsonResponse
{
    $request->authenticate();

    $user = Auth::user();

    $user->load('roles', 'permissions');

    $token = $user->createToken('auth-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => __('Login successful.'),
        'data' => [
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ],
    ]);
}

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => __('Logged out successfully.'),
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Resend verification code.
     */
    public function resendVerificationCode(Request $request,SmsMisrService $verificationService): JsonResponse
    {
        $data = $request->validate([
            'email' => ['sometimes', 'nullable', 'email', 'exists:users,email'],
            'phone' => ['sometimes', 'nullable', 'string', 'exists:users,phone'],
        ]);
        $user = null;
        if(isset($data['email'])){
            $user = User::where('email', $data['email'])->first();
        }else{
            $user = User::where('phone', $data['phone'])->first();
        }
        
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('User not found.'),
            ], 404);
        }
        if ($data['email']) {
            $user->sendEmailVerificationNotification();
        } elseif ($data['phone']) {
            $verificationService->sendPhoneVerification($user);
        }

        return response()->json([
            'success' => true,
            'message' => __('Verification code resent successfully.'),
        ]);
    }

    /**
     * Verify the user's account.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('User not found.'),
            ], 404);
        }

        if ($user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => __('Account already verified.'),
            ], 400);
        }

        $verification = Verification::where('user_id', $user->id)->where('type', 'email')->where('code', $data['code'])->first();
        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid verification code.'),
            ], 400);
        }

        $user->is_verified = true;
        $user->email_verified_at = now();
        $user->save();

        $verification->delete();

        // Create a new token
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('Account verified successfully.'),
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Verify the user's phone number.
     */
    public function verifyPhone(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);
        $user = User::where('phone', $data['phone'])->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('User not found.'),
            ], 404);
        }

        if ($user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => __('Account already verified.'),
            ], 400);
        }

        $verification = Verification::where('user_id', $user->id)->where('type', 'phone')->where('code', $data['code'])->first();
        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid verification code.'),
            ], 400);
        }

        $user->is_verified = true;
        $user->phone_verified_at = now();
        $user->save();

        $verification->delete();

        // Create a new token
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('Account verified successfully.'),
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }
    
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);
        $user = Auth::user();
        $user->update([
            'fcm_token' => $request->fcm_token
        ]);
        return response()->json([
            'message' => __('messages.fcm_token_updated_successfully')
        ]);
    }
    public function notifications()
    {
        $user = Auth::user();
        $notifications = $user->notifications;
        return response()->json([
            'data' => $notifications
        ]);
    }
    public function markAsRead($id)
    {
        $user = Auth::user();
    
        $notification = $user->notifications()->where('id', $id)->first();
    
        if (!$notification) {
            return response()->json([
                'message' => 'Notification not found'
            ], 404);
        }
    
        $notification->markAsRead();
    
        return response()->json([
            'message' => 'Notification marked as read'
        ]);
    }
    public function markAllAsRead()
    {
        $user = Auth::user();
    
        $user->unreadNotifications->markAsRead();
    
        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }
}
