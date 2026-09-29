<?php

namespace App\Http\Requests\Api\Payments;

use Illuminate\Foundation\Http\FormRequest;

class CreateFawryPaymentLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'return_url' => ['nullable', 'url', 'max:2000'],
            'language' => ['nullable', 'string', 'in:en-gb,ar-eg'],
            'payment_expiry' => ['nullable', 'integer', 'min:0'],
            'webhook_url' => ['nullable', 'url', 'max:2000'],
            'fawry_payment_method' => ['nullable', 'string', 'in:PayAtFawry,CARD,MWALLET,VALU,CashOnDelivery'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'return_url.url' => __('The return URL must be a valid URL.'),
            'webhook_url.url' => __('The webhook URL must be a valid URL.'),
            'language.in' => __('The language must be en-gb or ar-eg.'),
            'fawry_payment_method.in' => __('Invalid Fawry payment method.'),
        ];
    }
}
