<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Vendor;
use App\Models\VendorReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function reportProduct(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        ProductReport::create([
            'product_id' => $product->id,
            'user_id' => $user?->id,
            'reason' => $data['reason'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Product reported successfully.'),
        ]);
    }

    public function reportVendor(Request $request, Vendor $vendor): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        VendorReport::create([
            'vendor_id' => $vendor->id,
            'user_id' => $user?->id,
            'reason' => $data['reason'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Vendor reported successfully.'),
        ]);
    }
}
