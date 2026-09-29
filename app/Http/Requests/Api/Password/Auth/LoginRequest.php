<?php

namespace App\Http\Requests\Api\Password\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Verification;
use App\Models\User;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'], // Can be email or phone
            'code' => ['required', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => __('The login field is required (email or phone).'),
            'code.required' => __('The code field is required.'),
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
    
        $login = $this->input('login');
        $code = $this->input('code');
    
        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'phone';
    
        $user = User::where($field, $login)->first();
    
        if (! $user) {
            throw ValidationException::withMessages([
                'login' => [__('Invalid credentials.')],
            ]);
        }
    
        $verification = Verification::where('user_id', $user->id)
            ->where('type', 'phone')
            ->where('code', $code)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    
        if (! $verification) {
            RateLimiter::hit($this->throttleKey());
    
            throw ValidationException::withMessages([
                'code' => [__('Invalid or expired OTP code.')],
            ]);
        }
    
        $user->update([
            'is_verified' => true,
            'phone_verified_at' => now(),
        ]);
    
        $verification->delete();
    
        Auth::login($user);
    
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $key = $this->throttleKey();

        if (!RateLimiter::tooManyAttempts($key, 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'login' => [trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')).'|'.$this->ip());
    }
}
