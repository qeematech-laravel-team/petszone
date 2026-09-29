<?php

namespace App\Http\Requests\Admin\Plans;

use Illuminate\Foundation\Http\FormRequest;

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
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string', 'max:1000'],
            'description.ar' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'can_feature_products' => ['nullable', 'boolean'],
            'max_products_count' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
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
            'name.required' => 'The plan name is required.',
            'name.array' => 'The plan name must be an array for translations.',
            'name.en.required' => 'The English plan name is required.',
            'name.ar.required' => 'The Arabic plan name is required.',
            'name.en.string' => 'The English plan name must be a string.',
            'name.ar.string' => 'The Arabic plan name must be a string.',
            'name.en.max' => 'The English plan name must not exceed 255 characters.',
            'name.ar.max' => 'The Arabic plan name must not exceed 255 characters.',
            'description.array' => 'The plan description must be an array for translations.',
            'description.en.string' => 'The English description must be a string.',
            'description.ar.string' => 'The Arabic description must be a string.',
            'description.en.max' => 'The English description must not exceed 1000 characters.',
            'description.ar.max' => 'The Arabic description must not exceed 1000 characters.',
            'price.required' => 'The plan price is required.',
            'price.numeric' => 'The plan price must be a number.',
            'price.min' => 'The plan price must be greater than 0.',
            'duration_days.required' => 'The plan duration days is required.',
            'duration_days.integer' => 'The plan duration days must be an integer.',
            'duration_days.min' => 'The plan duration days must be greater than 0.',
            'can_feature_products.boolean' => 'The plan can feature products must be a boolean.',
            'max_products_count.integer' => 'The plan max products count must be an integer.',
            'max_products_count.min' => 'The plan max products count must be greater than 0.',
            'is_active.boolean' => 'The plan is active must be a boolean.',
            'is_featured.boolean' => 'The plan is featured must be a boolean.',
        ];
    }
}
