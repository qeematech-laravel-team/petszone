<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderRefundRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderRefundService
{
    public function __construct(protected OrderService $orderService) {}

    public function createRequestForUser(Order $order, int $userId, ?string $reason = null, ?string $details = null): OrderRefundRequest
    {
        if ($order->user_id !== $userId) {
            throw ValidationException::withMessages([
                'order' => [__('You are not allowed to refund this order.')],
            ]);
        }

        if ($order->status !== 'delivered') {
            throw ValidationException::withMessages([
                'order' => [__('Only delivered orders can be refunded.')],
            ]);
        }

        if ($order->refund_status === 'refunded') {
            throw ValidationException::withMessages([
                'order' => [__('This order has already been refunded.')],
            ]);
        }

        $existing = OrderRefundRequest::query()
            ->where('order_id', $order->id)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'order' => [__('You already have a pending refund request for this order.')],
            ]);
        }

        return OrderRefundRequest::create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'status' => 'pending',
            'reason' => $reason,
            'details' => $details,
        ]);
    }

    public function listForAdmin(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = OrderRefundRequest::query()
            ->with(['order', 'user', 'processor'])
            ->orderByDesc('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['order_id'])) {
            $query->where('order_id', (int) $filters['order_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function approve(OrderRefundRequest $requestModel): OrderRefundRequest
    {
        return DB::transaction(function () use ($requestModel) {
            if ($requestModel->status !== 'pending') {
                return $requestModel;
            }

            $order = Order::query()->lockForUpdate()->findOrFail($requestModel->order_id);

            $this->orderService->refundOrder($order, (int) $order->user_id);

            $requestModel->status = 'approved';
            $requestModel->processed_by = Auth::id();
            $requestModel->processed_at = now();
            $requestModel->save();

            return $requestModel;
        });
    }

    public function reject(OrderRefundRequest $requestModel, ?string $reason = null): OrderRefundRequest
    {
        if ($requestModel->status !== 'pending') {
            return $requestModel;
        }

        $requestModel->status = 'rejected';
        $requestModel->processed_by = Auth::id();
        $requestModel->processed_at = now();

        if ($reason) {
            $requestModel->reason = $reason;
        }

        $requestModel->save();

        return $requestModel;
    }
}
