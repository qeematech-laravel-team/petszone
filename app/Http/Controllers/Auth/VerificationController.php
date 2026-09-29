<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class VerificationController extends Controller
{
    /**
     * Show the verification form
     */
    public function show(Request $request)
    {
        return view('auth.verify-code');
    }

    /**
     * Verify the submitted code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:6'],
            'email' => ['nullable', 'email'],
        ]);

        $email = $request->email ?? $request->session()->get('email');

        if (! $email) {
            throw ValidationException::withMessages([
                'email' => 'Email is required.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'No user found with this email.',
            ]);
        }

        // Find the valid verification code
        $verification = Verification::where('user_id', $user->id)
            ->where('type', 'email')
            ->where('code', $request->code)
            ->valid()
            ->first();

        if (! $verification) {
            throw ValidationException::withMessages([
                'code' => 'Invalid or expired code.',
            ]);
        }

        // Mark verified
        $verification->markAsVerified();
        $user->markEmailAsVerified();
        $user->is_verified = true;
        $user->save();

        // Log in user if not already
        if (! Auth::check()) {
            Auth::login($user);
        }

        // Forget email from session
        $request->session()->forget('email');

        return redirect()->route('vendor.dashboard')->with('status', 'Email verified successfully!');
    }

    /**
     * Resend a verification code
     */
    public function resend(Request $request)
    {
        $email = $request->input('email') ?? $request->session()->get('email');

        if (! $email) {
            return redirect()->back()->withErrors(['email' => 'Email is required to resend code.']);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->back()->withErrors(['email' => 'No user found with this email.']);
        }


        $request->session()->put('email', $user->email);

        $user->sendEmailVerificationNotification();


        return redirect()->back()->with('status', 'Verification code resent successfully!');
    }
}
