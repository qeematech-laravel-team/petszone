<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\CustomerRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    /**
     * Get paginated customers for admin.
     *
     * @param  array{search?: string}  $filters
     */
    public function getPaginatedCustomers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->customerRepository->getPaginatedCustomers($perPage, $filters);
    }

    public function getCustomerWithOrders(int $userId, int $perPage = 15): array
    {
        $customer = $this->customerRepository->findWithStats($userId);
        $orders = $this->customerRepository->getPaginatedOrdersForCustomer($userId, $perPage);

        return [$customer, $orders];
    }

    /**
     * Get paginated customers for a specific vendor.
     *
     * @param  array{search?: string}  $filters
     */
    public function getPaginatedCustomersForVendor(int $vendorId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->customerRepository->getPaginatedCustomersForVendor($vendorId, $perPage, $filters);
    }

    public function getCustomerWithOrdersForVendor(int $userId, int $vendorId, int $perPage = 15): array
    {
        $customer = $this->customerRepository->findWithVendorStats($userId, $vendorId);
        $orders = $this->customerRepository->getPaginatedOrdersForCustomerAndVendor($userId, $vendorId, $perPage);

        return [$customer, $orders];
    }

    public function toggleCustomerActive(int $userId): ?User
    {
        $customer = $this->customerRepository->findWithStats($userId);

        if (! $customer) {
            return null;
        }

        $newStatus = ! (bool) $customer->is_active;

        $updated = $this->customerRepository->setActiveStatus($customer->id, $newStatus);

        if (! $updated) {
            return null;
        }

        if (! $newStatus) {
            if (method_exists($customer, 'tokens')) {
                $customer->tokens()->delete();
            }
        }

        return $customer->refresh();
    }

    /**
     * @param  array{name: string, email?: string|null, phone?: string|null}  $data
     */
    public function updateCustomerProfile(int $userId, array $data): ?User
    {
        $customer = $this->customerRepository->findWithStats($userId);

        if (! $customer) {
            return null;
        }

        return $this->customerRepository->updateProfile($customer, $data);
    }

    public function setCustomerPassword(int $userId, string $password): ?User
    {
        $customer = $this->customerRepository->findWithStats($userId);

        if (! $customer) {
            return null;
        }

        $customer->password = Hash::make($password);
        $customer->save();

        if (method_exists($customer, 'tokens')) {
            $customer->tokens()->delete();
        }

        return $customer->refresh();
    }

    public function adjustCustomerPoints(int $userId, string $type, int $amount, ?string $notes = null): ?User
    {
        return DB::transaction(function () use ($userId, $type, $amount, $notes) {
            $customer = $this->customerRepository->findCustomerForUpdate($userId);

            if (! $customer) {
                return null;
            }

            $currentPoints = (int) round((float) $customer->points);
            $newPoints = $type === 'addition'
                ? $currentPoints + $amount
                : $currentPoints - $amount;

            if ($newPoints < 0) {
                throw ValidationException::withMessages([
                    'amount' => __('Insufficient points for this operation.'),
                ]);
            }

            $customer->points = $newPoints;
            $customer->save();

            $this->customerRepository->addPointTransaction(
                $customer->id,
                $type,
                $amount,
                $newPoints,
                $notes
            );

            $customer->loadMissing(['pointTransactions' => function ($q) {
                $q->latest()->limit(10);
            }]);

            return $customer;
        });
    }
}
