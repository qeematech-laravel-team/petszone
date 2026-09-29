<?php

namespace App\Http\Requests\Vendor\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ProductPerformanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return vendorCan('view-product-performance-reports');
    }

    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'product_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'order_status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
