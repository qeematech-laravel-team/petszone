<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderRefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderRefundController extends Controller
{
    public function __construct(protected OrderRefundService $service) {}

    public function store(int $orderId, Request $request): JsonResponse
    {
        $user = Auth::user();

        $order = Order::query()->where('id', $orderId)->where('user_id', $user->id)->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => __('Order not found.'),
            ], 404);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $refundRequest = $this->service->createRequestForUser(
                $order,
                $user->id,
                $validated['reason'] ?? null,
                $validated['details'] ?? null
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('Unable to create refund request.'),
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __('Refund request submitted successfully.'),
            'data' => [
                'id' => $refundRequest->id,
                'status' => $refundRequest->status,
            ],
        ], 201);
    }
}
