<?php

namespace App\Http\Requests\Admin\Coupons;

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
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'type' => ['required', 'string', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_cart_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
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
            'code.required' => __('The coupon code is required.'),
            'code.unique' => __('This coupon code already exists.'),
            'code.max' => __('The coupon code must not exceed 50 characters.'),
            'type.required' => __('The coupon type is required.'),
            'type.in' => __('The coupon type must be either percentage or fixed.'),
            'discount_value.required' => __('The discount value is required.'),
            'discount_value.numeric' => __('The discount value must be a number.'),
            'discount_value.min' => __('The discount value must be at least 0.'),
            'min_cart_amount.numeric' => __('The minimum cart amount must be a number.'),
            'min_cart_amount.min' => __('The minimum cart amount must be at least 0.'),
            'usage_limit_per_user.integer' => __('The usage limit per user must be an integer.'),
            'usage_limit_per_user.min' => __('The usage limit per user must be at least 1.'),
            'start_date.date' => __('The start date must be a valid date.'),
            'end_date.date' => __('The end date must be a valid date.'),
            'end_date.after_or_equal' => __('The end date must be after or equal to the start date.'),
            'is_active.boolean' => __('The active status must be true or false.'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert code to uppercase
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim($this->code)),
            ]);
        }

        // Set default values
        $this->merge([
            'is_active' => $this->has('is_active') ? (bool) $this->is_active : true,
            'min_cart_amount' => $this->min_cart_amount ?? 0,
        ]);
    }
}
