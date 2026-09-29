<?php

namespace App\Http\Requests\Admin\Vendors;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust authorization logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vendor = $this->route('vendor');
        $ownerId = $vendor->owner_id ?? null;

        return [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'email', 'max:255', $ownerId ? 'unique:users,email,' . $ownerId : 'unique:users,email'],
            'owner_password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:3072'],
            'image_removed' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'subscription_start' => ['nullable', 'date'],
            'subscription_end' => ['nullable', 'date', 'after_or_equal:subscription_start'],
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
            'name.required' => 'The vendor name is required.',
            'name.array' => 'The vendor name must be an array for translations.',
            'name.en.required' => 'The English vendor name is required.',
            'name.ar.required' => 'The Arabic vendor name is required.',
            'name.en.string' => 'The English vendor name must be a string.',
            'name.ar.string' => 'The Arabic vendor name must be a string.',
            'name.en.max' => 'The English vendor name must not exceed 255 characters.',
            'name.ar.max' => 'The Arabic vendor name must not exceed 255 characters.',
            'owner_name.required' => 'The owner name is required.',
            'owner_name.string' => 'The owner name must be a string.',
            'owner_name.max' => 'The owner name must not exceed 255 characters.',
            'owner_email.required' => 'The owner email is required.',
            'owner_email.email' => 'The owner email must be a valid email address.',
            'owner_email.unique' => 'The owner email has already been taken.',
            'owner_password.min' => 'The owner password must be at least 8 characters.',
            'owner_password.confirmed' => 'The owner password confirmation does not match.',
            'phone.string' => 'The phone must be a string.',
            'phone.max' => 'The phone must not exceed 20 characters.',
            'address.string' => 'The address must be a string.',
            'address.max' => 'The address must not exceed 500 characters.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, svg, webp.',
            'image.max' => 'The image must not be larger than 3MB.',
            'is_active.boolean' => 'The active status must be a boolean.',
            'is_featured.boolean' => 'The featured status must be a boolean.',
            'balance.numeric' => 'The balance must be a number.',
            'balance.min' => 'The balance must be greater than or equal to 0.',
            'commission_rate.numeric' => 'The commission rate must be a number.',
            'commission_rate.min' => 'The commission rate must be greater than or equal to 0.',
            'commission_rate.max' => 'The commission rate must not exceed 100.',
            'plan_id.exists' => 'The selected plan does not exist.',
            'subscription_start.date' => 'The subscription start date must be a valid date.',
            'subscription_end.date' => 'The subscription end date must be a valid date.',
            'subscription_end.after_or_equal' => 'The subscription end date must be after or equal to the start date.',
        ];
    }
}
