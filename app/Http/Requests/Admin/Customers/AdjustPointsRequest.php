<?php

namespace App\Http\Requests\Admin\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return adminCan('manage-admin-customers');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['addition', 'subtraction'])],
            'amount' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => __('The type field is required.'),
            'amount.required' => __('The amount field is required.'),
            'amount.integer' => __('The amount must be an integer.'),
            'amount.min' => __('The amount must be at least 1.'),
        ];
    }
}
