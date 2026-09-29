<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\ForgetPasswordMail;
use App\Services\SmsMisrService;

class ResetPasswordController extends Controller
{
    /**
     * Step 1: Send OTP Code
     */
    public function resetPasswordSendCode(Request $request,SmsMisrService $verificationService)
    {
        $data = $request->validate([
            'email' => ['nullable','email','exists:users,email'],
            'phone' => ['nullable','string','exists:users,phone'],
        ]);

        if (empty($data['email']) && empty($data['phone'])) {
            return response()->json([
                'success' => false,
                'message' => __('Email or phone is required.'),
            ], 422);
        }

        // Find user safely
        $user = User::where(function ($query) use ($data) {
            if (!empty($data['email'])) $query->where('email', $data['email']);
            if (!empty($data['phone'])) $query->orWhere('phone', $data['phone']);
        })->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('User not found.'),
            ], 404);
        }

        // Generate 6-digit OTP code
        $code = random_int(100000, 999999);

        // Store code in DB with expiry
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email, 'phone' => $user->phone],
            [
                'token' => $code,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(10),
            ]
        );

        // Send code via email (or SMS if phone)
        if (!empty($user->email)) {
            Mail::to($user->email)->send(new ForgetPasswordMail($code));
        }else{
            $verificationService->sendResetPasswordOTP($user);
        }

        return response()->json([
            'success' => true,
            'message' => __('Password reset code sent successfully.'),
        ]);
    }

    /**
     * Step 2: Verify OTP Code
     */
    public function resetPasswordVerifyCode(Request $request)
    {
        $data = $request->validate([
            'email' => ['nullable','email','exists:users,email'],
            'phone' => ['nullable','string','exists:users,phone'],
            'code'  => ['required','digits:6'],
        ]);

        // Find user
        $user = User::where(function ($query) use ($data) {
            if (!empty($data['email'])) $query->where('email', $data['email']);
            if (!empty($data['phone'])) $query->orWhere('phone', $data['phone']);
        })->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('User not found.'),
            ], 404);
        }

        // Verify OTP and expiry
        $passwordResetToken = DB::table('password_reset_tokens')
            ->where(function($query) use ($user){
                if ($user->email) $query->where('email', $user->email);
                if ($user->phone) $query->orWhere('phone', $user->phone);
            })
            ->where('token', $data['code'])
            ->where('expires_at', '>', now())
            ->first();

        if (!$passwordResetToken) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid or expired verification code.'),
            ], 400);
        }

        // Generate reset_token for setting new password
        $resetToken = Str::uuid();

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->orWhere('phone', $user->phone)
            ->update([
                'token' => $resetToken,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(30), // reset token expiry
            ]);

        return response()->json([
            'success' => true,
            'message' => __('Password reset code verified successfully.'),
            'data' => [
                'reset_token' => $resetToken,
            ],
        ]);
    }

    /**
     * Step 3: Set New Password
     */
    public function resetPasswordSetNewPassword(Request $request)
    {
        $data = $request->validate([
            'reset_token' => ['required','string'],
            'password'    => ['required','string','confirmed'],
        ]);

        // Find token
        $passwordResetToken = DB::table('password_reset_tokens')
            ->where('token', $data['reset_token'])
            ->where('expires_at', '>', now())
            ->first();

        if (!$passwordResetToken) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid or expired reset token.'),
            ], 400);
        }

        // Find user
        $user = User::where(function($query) use ($passwordResetToken){
            if ($passwordResetToken->email) $query->where('email', $passwordResetToken->email);
            if ($passwordResetToken->phone) $query->orWhere('phone', $passwordResetToken->phone);
        })->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('User not found.'),
            ], 404);
        }

        // Update password
        $user->password = Hash::make($data['password']);
        $user->save();

        // Delete reset token
        DB::table('password_reset_tokens')
            ->where('token', $data['reset_token'])
            ->delete();

        // Optional: create API token for mobile login
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('Password reset successfully. Please login with your new password.'),
            'data' => [
                'user' => $user, // or wrap in Resource
                'token' => $token,
            ],
        ]);
    }
}
