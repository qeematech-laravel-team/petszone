<?php

namespace App\Http\Requests\Auth\Vendors;

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
        $rules = [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'address' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:3072'],
            'plan_id' => ['nullable', 'exists:plans,id'],
        ];

        // If user is not authenticated, require email and password
        if (!auth()->check()) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:users,email'];
            $rules['password'] = ['required', 'string', Password::defaults(), 'confirmed'];
            $rules['owner_name'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The vendor name is required.',
            'name.array' => 'The vendor name must be an array for translations.',
            'name.en.required' => 'The English vendor name is required.',
            'name.ar.required' => 'The Arabic vendor name is required.',
            'name.en.string' => 'The English vendor name must be a string.',
            'name.ar.string' => 'The Arabic vendor name must be a string.',
            'name.en.max' => 'The English vendor name must not exceed 255 characters.',
            'name.ar.max' => 'The Arabic vendor name must not exceed 255 characters.',
            'email.required' => 'The email address is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password is required.',
            'password.confirmed' => 'The password confirmation does not match.',
            'owner_name.required' => 'The owner name is required.',
            'phone.string' => 'The phone must be a string.',
            'phone.max' => 'The phone must not exceed 20 characters.',
            'phone.unique' => 'The phone number has already been taken.',
            'address.string' => 'The address must be a string.',
            'address.max' => 'The address must not exceed 500 characters.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, svg, webp.',
            'image.max' => 'The image must not be larger than 3MB.',
            'plan_id.exists' => 'The selected plan does not exist.',
        ];
    }
}
