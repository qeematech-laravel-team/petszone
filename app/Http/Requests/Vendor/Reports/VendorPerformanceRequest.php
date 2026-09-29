<?php

namespace App\Http\Requests\Vendor\Reports;

use Illuminate\Foundation\Http\FormRequest;

class VendorPerformanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return vendorCan('view-vendor-performance-reports');
    }

    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'order_status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
