<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    /**
     * Get paginated customers for admin with global stats.
     *
     * @param  array{
     *   search?: string,
     *   status?: string,
     *   from_date?: string,
     *   to_date?: string,
     *   min_orders_count?: int|string,
     *   max_orders_count?: int|string,
     *   min_orders_total?: float|int|string,
     *   max_orders_total?: float|int|string,
     *   sort?: string,
     * }  $filters
     */
    public function getPaginatedCustomers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->where('role', 'user')
            ->withCount('orders')
            ->withSum('orders as orders_total', 'total')
            ->orderByDesc('id');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (isset($filters['min_orders_count']) && $filters['min_orders_count'] !== '') {
            $query->having('orders_count', '>=', $filters['min_orders_count']);
        }

        if (isset($filters['max_orders_count']) && $filters['max_orders_count'] !== '') {
            $query->having('orders_count', '<=', $filters['max_orders_count']);
        }

        if (isset($filters['min_orders_total']) && $filters['min_orders_total'] !== '') {
            $query->having('orders_total', '>=', $filters['min_orders_total']);
        }

        if (isset($filters['max_orders_total']) && $filters['max_orders_total'] !== '') {
            $query->having('orders_total', '<=', $filters['max_orders_total']);
        }

        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->orderBy('id', 'asc'),
            'orders_count_desc' => $query->orderByDesc('orders_count'),
            'orders_total_desc' => $query->orderByDesc('orders_total'),
            default => $query->orderByDesc('id'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function findWithStats(int $userId): ?User
    {
        return User::query()
            ->where('role', 'user')
            ->withCount('orders')
            ->withSum('orders as orders_total', 'total')
            ->with(['pointTransactions' => function ($q) {
                $q->latest()->limit(10);
            }])
            ->find($userId);
    }

    public function findCustomerForUpdate(int $userId): ?User
    {
        return User::query()
            ->where('id', $userId)
            ->where('role', 'user')
            ->lockForUpdate()
            ->first();
    }

    public function setActiveStatus(int $userId, bool $isActive): bool
    {
        return User::query()
            ->where('id', $userId)
            ->where('role', 'user')
            ->update(['is_active' => $isActive]) > 0;
    }

    /**
     * @param  array{name: string, email?: string|null, phone?: string|null}  $data
     */
    public function updateProfile(User $customer, array $data): User
    {
        $customer->fill([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        $customer->save();

        return $customer->refresh();
    }

    public function addPointTransaction(int $userId, string $type, int $amount, int $balanceAfter, ?string $notes = null): PointTransaction
    {
        return PointTransaction::create([
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'notes' => $notes,
        ]);
    }

    /**
     * @return Collection<int, PointTransaction>
     */
    public function recentPointTransactions(int $userId, int $limit = 10): Collection
    {
        return PointTransaction::query()
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get customers who placed orders with a specific vendor.
     *
     * @param  array{
     *   search?: string,
     *   from_date?: string,
     *   to_date?: string,
     *   min_orders_count?: int|string,
     *   max_orders_count?: int|string,
     *   min_orders_total?: float|int|string,
     *   max_orders_total?: float|int|string,
     *   sort?: string,
     * }  $filters
     */
    public function getPaginatedCustomersForVendor(int $vendorId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->whereHas('orders.vendorOrders', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->withCount(['orders as orders_count_for_vendor' => function ($q) use ($vendorId) {
                $q->whereHas('vendorOrders', function ($sub) use ($vendorId) {
                    $sub->where('vendor_id', $vendorId);
                });
            }])
            ->withSum(['orders as orders_total_for_vendor' => function ($q) use ($vendorId) {
                $q->whereHas('vendorOrders', function ($sub) use ($vendorId) {
                    $sub->where('vendor_id', $vendorId);
                });
            }], 'total')
            ->orderByDesc('id');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (isset($filters['min_orders_count']) && $filters['min_orders_count'] !== '') {
            $query->having('orders_count_for_vendor', '>=', $filters['min_orders_count']);
        }

        if (isset($filters['max_orders_count']) && $filters['max_orders_count'] !== '') {
            $query->having('orders_count_for_vendor', '<=', $filters['max_orders_count']);
        }

        if (isset($filters['min_orders_total']) && $filters['min_orders_total'] !== '') {
            $query->having('orders_total_for_vendor', '>=', $filters['min_orders_total']);
        }

        if (isset($filters['max_orders_total']) && $filters['max_orders_total'] !== '') {
            $query->having('orders_total_for_vendor', '<=', $filters['max_orders_total']);
        }

        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->orderBy('id', 'asc'),
            'orders_count_desc' => $query->orderByDesc('orders_count_for_vendor'),
            'orders_total_desc' => $query->orderByDesc('orders_total_for_vendor'),
            default => $query->orderByDesc('id'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function findWithVendorStats(int $userId, int $vendorId): ?User
    {
        return User::query()
            ->withCount(['orders as orders_count_for_vendor' => function ($q) use ($vendorId) {
                $q->whereHas('vendorOrders', function ($sub) use ($vendorId) {
                    $sub->where('vendor_id', $vendorId);
                });
            }])
            ->withSum(['orders as orders_total_for_vendor' => function ($q) use ($vendorId) {
                $q->whereHas('vendorOrders', function ($sub) use ($vendorId) {
                    $sub->where('vendor_id', $vendorId);
                });
            }], 'total')
            ->find($userId);
    }

    /**
     * Get paginated orders for a customer (admin context).
     */
    public function getPaginatedOrdersForCustomer(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $userId)
            ->with(['vendorOrders.vendor'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get paginated orders for a customer limited to a vendor (vendor context).
     */
    public function getPaginatedOrdersForCustomerAndVendor(int $userId, int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $userId)
            ->whereHas('vendorOrders', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->with(['vendorOrders' => function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)->with('vendor');
            }])
            ->latest()
            ->paginate($perPage);
    }
}
