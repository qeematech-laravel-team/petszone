<?php

namespace App\Http\Requests\Vendor\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return vendorCan('manage-settings');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'allow_branch_user_to_edit_stock' => ['nullable', 'boolean'],
            'allow_branch_user_to_edit_price' => ['nullable', 'boolean'],
            'enable_inventory_alerts' => ['nullable', 'boolean'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'allow_free_shipping_threshold' => ['nullable', 'boolean'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost_per_km' => ['nullable', 'numeric', 'min:0'],
            'minimum_shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'maximum_shipping_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
