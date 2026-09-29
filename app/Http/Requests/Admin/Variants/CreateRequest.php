<?php

namespace App\Http\Requests\Admin\Variants;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
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
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'options' => ['nullable', 'array'],
            'options.*.name' => ['required', 'array'],
            'options.*.name.en' => ['required_with:options.*.name', 'string', 'max:255'],
            'options.*.name.ar' => ['required_with:options.*.name', 'string', 'max:255'],
            'options.*.code' => ['nullable', 'string', 'max:255', 'unique:variant_options,code'],
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
            'name.*.string' => __('Each translation must be a string.'),
            'name.*.max' => __('Each translation must not exceed 255 characters.'),
            'is_required.boolean' => __('The required field must be true or false.'),
            'is_active.boolean' => __('The active field must be true or false.'),
            'options.array' => __('Options must be an array.'),
            'options.*.name.required' => __('Each option name is required.'),
            'options.*.name.array' => __('Each option name must be an array for translations.'),
            'options.*.name.en.required_with' => __('The English name is required for each option.'),
            'options.*.name.ar.required_with' => __('The Arabic name is required for each option.'),
            'options.*.name.*.string' => __('Each option translation must be a string.'),
            'options.*.name.*.max' => __('Each option translation must not exceed 255 characters.'),
            'options.*.code.string' => __('Option code must be a string.'),
            'options.*.code.max' => __('Option code must not exceed 255 characters.'),
            'options.*.code.unique' => __('This option code is already taken.'),
        ];
    }
}
