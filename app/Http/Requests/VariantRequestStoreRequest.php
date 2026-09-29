<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VariantRequestStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Vendor role has all permissions, vendor_employee needs specific permission
        return $this->user()->hasRole('vendor') ||
               ($this->user()->hasRole('vendor_employee') && $this->user()->hasPermissionTo('create-variant-requests'));
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
            'options' => ['nullable', 'array'],
            'options.*.name' => ['required', 'array'],
            'options.*.name.en' => ['required_with:options.*.name', 'string', 'max:255'],
            'options.*.name.ar' => ['required_with:options.*.name', 'string', 'max:255'],
            'options.*.code' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
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
            'name.required' => __('The variant name is required.'),
            'name.array' => __('The variant name must be an array for translations.'),
            'name.en.required' => __('The English name is required.'),
            'name.ar.required' => __('The Arabic name is required.'),
            'options.array' => __('Options must be an array.'),
            'options.*.name.required' => __('Each option name is required.'),
            'options.*.name.array' => __('Each option name must be an array for translations.'),
            'options.*.name.en.required_with' => __('The English name is required for each option.'),
            'options.*.name.ar.required_with' => __('The Arabic name is required for each option.'),
            'description.max' => __('The description must not exceed 1000 characters.'),
        ];
    }
}
