<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository
{
    /**
     * Get paginated orders with filters.
     *
     * @param  array{
     *   search?: string,
     *   status?: string,
     *   payment_status?: string,
     *   payment_method?: string,
     *   refund_status?: string,
     *   user_id?: int|string,
     *   vendor_id?: int|string,
     *   branch_id?: int|string,
     *   coupon_id?: int|string,
     *   address_id?: int|string,
     *   vendor_order_status?: string,
     *   from_date?: string,
     *   to_date?: string,
     *   min_total?: float|int|string,
     *   max_total?: float|int|string,
     *   sort?: string,
     * }  $filters
     */
    public function getPaginatedOrders(int $perPage = 15, array $filters = [], bool $activeProductsOnly = false): LengthAwarePaginator
    {
        $query = Order::query()->with([
            'user',
            'coupon',
            'address',
            'vendorOrders.vendor' => fn ($vendorQuery) => Vendor::constrainVisibleRatingAggregates($vendorQuery),
            'vendorOrders.branch',
            'vendorOrders.items.product' => $this->productEagerLoadConstraint($activeProductsOnly),
            'vendorOrders.items.variant',
        ]);

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_status']) && $filters['payment_status'] !== '') {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['payment_method']) && $filters['payment_method'] !== '') {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['refund_status']) && $filters['refund_status'] !== '') {
            $query->where('refund_status', $filters['refund_status']);
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['coupon_id']) && $filters['coupon_id'] !== '') {
            $query->where('coupon_id', $filters['coupon_id']);
        }

        if (isset($filters['address_id']) && $filters['address_id'] !== '') {
            $query->where('address_id', $filters['address_id']);
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (isset($filters['min_total']) && $filters['min_total'] !== '') {
            $query->where('total', '>=', $filters['min_total']);
        }

        if (isset($filters['max_total']) && $filters['max_total'] !== '') {
            $query->where('total', '<=', $filters['max_total']);
        }

        if (isset($filters['vendor_id']) && $filters['vendor_id'] !== '') {
            $query->whereHas('vendorOrders', function ($vendorOrdersQuery) use ($filters) {
                $vendorOrdersQuery->where('vendor_id', $filters['vendor_id']);
            });
        }

        if (isset($filters['branch_id']) && $filters['branch_id'] !== '') {
            $query->whereHas('vendorOrders', function ($vendorOrdersQuery) use ($filters) {
                $vendorOrdersQuery->where('branch_id', $filters['branch_id']);
            });
        }

        if (isset($filters['vendor_order_status']) && $filters['vendor_order_status'] !== '') {
            $vendorId = $filters['vendor_id'] ?? null;
            $branchId = $filters['branch_id'] ?? null;

            $query->whereHas('vendorOrders', function ($vendorOrdersQuery) use ($filters, $vendorId, $branchId) {
                $vendorOrdersQuery->where('status', $filters['vendor_order_status']);

                if ($vendorId) {
                    $vendorOrdersQuery->where('vendor_id', $vendorId);
                }

                if ($branchId) {
                    $vendorOrdersQuery->where('branch_id', $branchId);
                }
            });
        }

        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->oldest(),
            'total_asc' => $query->orderBy('total', 'asc'),
            'total_desc' => $query->orderBy('total', 'desc'),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    /**
     * Get order by id with relations.
     */
    public function getOrderById(int $id): ?Order
    {
        return Order::with([
            'user',
            'coupon',
            'address',
            'vendorOrders.vendor',
            'vendorOrders.branch',
            'vendorOrders.items.product',
            'vendorOrders.items.variant',
        ])->find($id);
    }

    /**
     * Get orders by user id.
     */
    public function getOrdersByUser(int $userId): Collection
    {
        return Order::query()->where('user_id', '=', $userId, 'and')
            ->with([
                'coupon',
                'address',
                'vendorOrders.vendor',
                'vendorOrders.branch',
            ])
            ->latest()
            ->get();
    }

    /**
     * Get paginated orders for a specific user.
     *
     * @param  array{
     *   search?: string,
     *   status?: string,
     *   payment_status?: string,
     *   payment_method?: string,
     *   refund_status?: string,
     *   vendor_id?: int|string,
     *   branch_id?: int|string,
     *   from_date?: string,
     *   to_date?: string,
     *   min_total?: float|int|string,
     *   max_total?: float|int|string,
     *   sort?: string,
     * }  $filters
     */
    public function getPaginatedOrdersForUser(int $userId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $filters['user_id'] = $userId;

        return $this->getPaginatedOrders($perPage, $filters, activeProductsOnly: true);
    }

    /**
     * Get order by id for a specific user.
     */
    public function getOrderByIdForUser(int $id, int $userId): ?Order
    {
        return Order::with([
            'user',
            'coupon',
            'address',
            'vendorOrders.vendor' => fn ($vendorQuery) => Vendor::constrainVisibleRatingAggregates($vendorQuery),
            'vendorOrders.branch',
            'vendorOrders.items.product' => $this->productEagerLoadConstraint(activeProductsOnly: true),
            'vendorOrders.items.variant',
        ])->where('user_id', $userId)->find($id);
    }

    /**
     * Get paginated orders for a specific vendor (loads only this vendor's vendorOrders).
     *
     * @param  array{
     *   search?: string,
     *   status?: string,
     *   vendor_order_status?: string,
     *   branch_id?: int|string,
     *   from_date?: string,
     *   to_date?: string,
     *   min_total?: float|int|string,
     *   max_total?: float|int|string,
     *   payment_status?: string,
     *   payment_method?: string,
     *   refund_status?: string,
     *   sort?: string,
     * }  $filters
     */
    public function getPaginatedOrdersForVendor(int $vendorId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $filters['vendor_id'] = $vendorId;

        $query = Order::query()
            ->whereHas('vendorOrders', function ($vendorOrdersQuery) use ($vendorId) {
                $vendorOrdersQuery->where('vendor_id', $vendorId);
            })
            ->with([
                'user',
                'coupon',
                'address',
                'vendorOrders' => function ($vendorOrdersQuery) use ($vendorId) {
                    $vendorOrdersQuery->where('vendor_id', $vendorId)
                        ->with([
                            'vendor',
                            'branch',
                            'items.product',
                            'items.variant',
                        ]);
                },
            ]);

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_status']) && $filters['payment_status'] !== '') {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['payment_method']) && $filters['payment_method'] !== '') {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['refund_status']) && $filters['refund_status'] !== '') {
            $query->where('refund_status', $filters['refund_status']);
        }

        if (isset($filters['branch_id']) && $filters['branch_id'] !== '') {
            $branchId = $filters['branch_id'];
            $query->whereHas('vendorOrders', function ($vendorOrdersQuery) use ($vendorId, $branchId) {
                $vendorOrdersQuery->where('vendor_id', $vendorId)->where('branch_id', $branchId);
            });
        }

        if (isset($filters['vendor_order_status']) && $filters['vendor_order_status'] !== '') {
            $vendorOrderStatus = $filters['vendor_order_status'];
            $query->whereHas('vendorOrders', function ($vendorOrdersQuery) use ($vendorId, $vendorOrderStatus) {
                $vendorOrdersQuery->where('vendor_id', $vendorId)->where('status', $vendorOrderStatus);
            });
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (isset($filters['min_total']) && $filters['min_total'] !== '') {
            $query->where('total', '>=', $filters['min_total']);
        }

        if (isset($filters['max_total']) && $filters['max_total'] !== '') {
            $query->where('total', '<=', $filters['max_total']);
        }

        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->oldest(),
            'total_asc' => $query->orderBy('total', 'asc'),
            'total_desc' => $query->orderBy('total', 'desc'),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    /**
     * Get order by id for a specific vendor (loads only this vendor's vendorOrders).
     */
    public function getOrderByIdForVendor(int $id, int $vendorId): ?Order
    {
        return Order::query()
            ->whereHas('vendorOrders', function ($vendorOrdersQuery) use ($vendorId) {
                $vendorOrdersQuery->where('vendor_id', $vendorId);
            })
            ->with([
                'user',
                'coupon',
                'address',
                'vendorOrders' => function ($vendorOrdersQuery) use ($vendorId) {
                    $vendorOrdersQuery->where('vendor_id', $vendorId)
                        ->with([
                            'vendor',
                            'branch',
                            'items.product',
                            'items.variant',
                        ]);
                },
            ])
            ->find($id);
    }

    /**
     * Get paginated vendor orders for a specific vendor.
     *
     * @param  array{
     *   search?: string,
     *   status?: string,
     *   branch_id?: int|string,
     *   order_id?: int|string,
     *   from_date?: string,
     *   to_date?: string,
     * }  $filters
     */
    public function getPaginatedVendorOrdersForVendor(int $vendorId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = VendorOrder::query()
            ->where('vendor_id', $vendorId)
            ->with([
                'order.user',
                'order.address',
                'vendor',
                'branch',
                'items.product',
                'items.variant',
            ]);

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('order_id', $search)
                    ->orWhereHas('order.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['order_id']) && $filters['order_id'] !== '') {
            $query->where('order_id', $filters['order_id']);
        }

        if (isset($filters['branch_id']) && $filters['branch_id'] !== '') {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (isset($filters['min_total']) && $filters['min_total'] !== '') {
            $query->where('total', '>=', $filters['min_total']);
        }

        if (isset($filters['max_total']) && $filters['max_total'] !== '') {
            $query->where('total', '<=', $filters['max_total']);
        }

        if (isset($filters['payment_status']) && $filters['payment_status'] !== '') {
            $query->whereHas('order', function ($orderQuery) use ($filters) {
                $orderQuery->where('payment_status', $filters['payment_status']);
            });
        }

        if (isset($filters['payment_method']) && $filters['payment_method'] !== '') {
            $query->whereHas('order', function ($orderQuery) use ($filters) {
                $orderQuery->where('payment_method', $filters['payment_method']);
            });
        }

        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->oldest(),
            'total_asc' => $query->orderBy('total', 'asc'),
            'total_desc' => $query->orderBy('total', 'desc'),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    /**
     * Get vendor order by id for a specific vendor.
     */
    public function getVendorOrderByIdForVendor(int $id, int $vendorId): ?VendorOrder
    {
        return VendorOrder::query()
            ->where('vendor_id', $vendorId)
            ->with([
                'order.user',
                'order.address',
                'vendor',
                'branch',
                'items.product',
                'items.variant',
            ])
            ->find($id);
    }

    /**
     * Create a new order.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Update an order.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    /**
     * Delete an order (soft delete).
     */
    public function delete(Order $order): bool
    {
        return Order::destroy($order->id) > 0;
    }

    /**
     * @return \Closure(\Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Eloquent\Builder): (\Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Eloquent\Builder)
     */
    private function productEagerLoadConstraint(bool $activeProductsOnly = false): \Closure
    {
        return fn ($productQuery) => $activeProductsOnly
            ? Product::constrainApiVisible($productQuery->with(Product::apiListRelations()))
            : Product::constrainFavoriteExists($productQuery->with(Product::apiListRelations()));
    }
}
