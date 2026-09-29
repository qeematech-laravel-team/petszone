<?php

namespace App\Http\Requests\Api\Password\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:'.User::class,
            ],
            'phone' => [
                'required',
                'string',
                'max:255',
                'unique:'.User::class,
            ],
            // 'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'referred_by_code' => ['nullable', 'string', 'exists:users,referral_code'],
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
            'name.required' => __('The name field is required.'),
            'name.string' => __('The name must be a string.'),
            'name.max' => __('The name must not exceed 255 characters.'),
            'email.required' => __('The email field is required.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.lowercase' => __('The email must be lowercase.'),
            'email.unique' => __('The email has already been taken.'),
            'phone.unique' => __('The phone has already been taken.'),
            'password.required' => __('The password field is required.'),
            'password.confirmed' => __('The password confirmation does not match.'),
            'referred_by_code.exists' => __('The referred by code is invalid.'),
        ];
    }
}
