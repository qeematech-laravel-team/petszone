<?php

namespace App\Http\Requests\Vendor\Branches;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Vendor role has all permissions, vendor_employee needs specific permission
        return $this->user()->hasRole('vendor') ||
               ($this->user()->hasRole('vendor_employee') && $this->user()->hasPermissionTo('create-branches'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'string', 'max:50'],
            'longitude' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
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
            'name.required' => __('The branch name is required.'),
            'name.array' => __('The branch name must be an array for translations.'),
            'name.en.required' => __('The English name is required.'),
            'name.ar.required' => __('The Arabic name is required.'),
            'address.required' => __('The address field is required.'),
            'address.max' => __('The address must not exceed 500 characters.'),
            'latitude.max' => __('The latitude must not exceed 50 characters.'),
            'longitude.max' => __('The longitude must not exceed 50 characters.'),
            'phone.max' => __('The phone must not exceed 20 characters.'),
        ];
    }
}
