<?php

namespace App\Services;

use App\Models\User;
use App\Models\VendorUser;
use App\Repositories\VendorUserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class VendorUserService
{
    /**
     * Vendor-scoped permissions that can be assigned to vendor employees.
     *
     * @var array<string, array{label: string, group: string}>
     */
    public const PERMISSIONS = [
        'view-dashboard' => ['label' => 'View Dashboard', 'group' => 'Dashboard'],
        'view-branch-dashboard' => ['label' => 'View Branch Dashboard', 'group' => 'Dashboard'],
        'edit-profile' => ['label' => 'Edit Vendor Profile', 'group' => 'Account'],
        'manage-settings' => ['label' => 'Manage Vendor Settings', 'group' => 'Account'],

        'view-products' => ['label' => 'View Products', 'group' => 'Products'],
        'create-products' => ['label' => 'Create Products', 'group' => 'Products'],
        'edit-products' => ['label' => 'Edit Products', 'group' => 'Products'],
        'delete-products' => ['label' => 'Delete Products', 'group' => 'Products'],
        'manage-products' => ['label' => 'Manage Products', 'group' => 'Products'],
        'manage-product-stock' => ['label' => 'Manage Product Stock', 'group' => 'Products'],
        'manage-product-prices' => ['label' => 'Manage Product Prices', 'group' => 'Products'],
        'view-product-ratings' => ['label' => 'View Product Ratings', 'group' => 'Products'],
        'manage-product-ratings' => ['label' => 'Manage Product Ratings', 'group' => 'Products'],
        'view-product-reports' => ['label' => 'View Product Reports', 'group' => 'Products'],
        'manage-product-reports' => ['label' => 'Manage Product Reports', 'group' => 'Products'],

        'view-branches' => ['label' => 'View Branches', 'group' => 'Branches'],
        'create-branches' => ['label' => 'Create Branches', 'group' => 'Branches'],
        'edit-branches' => ['label' => 'Edit Branches', 'group' => 'Branches'],
        'delete-branches' => ['label' => 'Delete Branches', 'group' => 'Branches'],
        'manage-branches' => ['label' => 'Manage Branches', 'group' => 'Branches'],

        'view-categories' => ['label' => 'View Categories', 'group' => 'Catalog'],
        'view-variants' => ['label' => 'View Variants', 'group' => 'Catalog'],
        'view-category-requests' => ['label' => 'View Category Requests', 'group' => 'Requests'],
        'create-category-requests' => ['label' => 'Create Category Requests', 'group' => 'Requests'],
        'view-variant-requests' => ['label' => 'View Variant Requests', 'group' => 'Requests'],
        'create-variant-requests' => ['label' => 'Create Variant Requests', 'group' => 'Requests'],

        'view-plans' => ['label' => 'View Plans', 'group' => 'Subscriptions'],
        'subscribe-plans' => ['label' => 'Subscribe to Plans', 'group' => 'Subscriptions'],
        'view-subscriptions' => ['label' => 'View Subscriptions', 'group' => 'Subscriptions'],
        'cancel-subscriptions' => ['label' => 'Cancel Subscriptions', 'group' => 'Subscriptions'],

        'view-orders' => ['label' => 'View Orders', 'group' => 'Sales'],
        'edit-orders' => ['label' => 'Edit Orders', 'group' => 'Sales'],
        'update-order-status' => ['label' => 'Update Order Status', 'group' => 'Sales'],
        'view-order-invoices' => ['label' => 'View Order Invoices', 'group' => 'Sales'],
        'view-customers' => ['label' => 'View Customers', 'group' => 'Sales'],
        'view-withdrawals' => ['label' => 'View Withdrawals', 'group' => 'Finance'],
        'create-withdrawals' => ['label' => 'Request Withdrawals', 'group' => 'Finance'],

        'view-reports' => ['label' => 'View Reports', 'group' => 'Reports'],
        'view-earnings-reports' => ['label' => 'View Earnings Reports', 'group' => 'Reports'],
        'view-product-performance-reports' => ['label' => 'View Product Performance Reports', 'group' => 'Reports'],
        'view-vendor-performance-reports' => ['label' => 'View Vendor Performance Reports', 'group' => 'Reports'],

        'view-tickets' => ['label' => 'View Tickets', 'group' => 'Support'],
        'create-tickets' => ['label' => 'Create Tickets', 'group' => 'Support'],
        'edit-tickets' => ['label' => 'Edit Tickets', 'group' => 'Support'],
        'delete-tickets' => ['label' => 'Delete Tickets', 'group' => 'Support'],
        'reply-tickets' => ['label' => 'Reply to Tickets', 'group' => 'Support'],
        'update-ticket-status' => ['label' => 'Update Ticket Status', 'group' => 'Support'],
        'manage-tickets' => ['label' => 'Manage Tickets', 'group' => 'Support'],

        'view-vendor-users' => ['label' => 'View Team Members', 'group' => 'Team'],
        'create-vendor-users' => ['label' => 'Create Team Members', 'group' => 'Team'],
        'edit-vendor-users' => ['label' => 'Edit Team Members', 'group' => 'Team'],
        'delete-vendor-users' => ['label' => 'Delete Team Members', 'group' => 'Team'],
        'manage-vendor-users' => ['label' => 'Manage Team Members', 'group' => 'Team'],
    ];

    public function __construct(protected VendorUserRepository $vendorUserRepository) {}

    /**
     * @return list<string>
     */
    public static function permissionNames(): array
    {
        return array_keys(self::PERMISSIONS);
    }

    public function getVendorUsersByVendor(int $vendorId): Collection
    {
        return $this->vendorUserRepository->getVendorUsersByVendor($vendorId);
    }

    public function getPaginatedVendorUsersByVendor(int $vendorId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->vendorUserRepository->getPaginatedVendorUsersByVendor($vendorId, $perPage, $filters);
    }

    public function getVendorUserById(int $id): ?VendorUser
    {
        return $this->vendorUserRepository->getVendorUserById($id);
    }

    /**
     * Grouped permissions available for assignment by the current actor.
     *
     * @return SupportCollection<string, SupportCollection<int, Permission>>
     */
    public function groupedAssignablePermissions(?User $actor = null): SupportCollection
    {
        return $this->assignablePermissions($actor)
            ->groupBy(fn (Permission $permission): string => self::PERMISSIONS[$permission->name]['group'] ?? 'Other')
            ->sortKeys();
    }

    /**
     * @return SupportCollection<int, Permission>
     */
    public function assignablePermissions(?User $actor = null): SupportCollection
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', self::permissionNames())
            ->get()
            ->sortBy(fn (Permission $permission): int => array_search($permission->name, self::permissionNames(), true))
            ->values();

        if (! $actor || $actor->hasRole('vendor') || $actor->hasRole('admin')) {
            return $permissions;
        }

        return $permissions
            ->filter(fn (Permission $permission): bool => $actor->hasPermissionTo($permission->name))
            ->values();
    }

    /**
     * @param  array{name: string, email?: ?string, phone?: ?string, password: string, is_active?: bool, permissions?: array<int, string>, user_type?: string, branch_id?: ?int}  $data
     */
    public function createVendorUser(int $vendorId, array $data, ?User $actor = null): VendorUser
    {
        return DB::transaction(function () use ($vendorId, $data, $actor): VendorUser {
            if (isset($data['user_id'])) {
                if ($this->vendorUserRepository->userExistsForVendor($vendorId, $data['user_id'])) {
                    throw new \Exception(__('User is already associated with this vendor.'));
                }
            } else {
                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'password' => $data['password'],
                    'is_active' => true,
                    'is_verified' => false,
                    'role' => 'vendor',
                ]);

                Role::findOrCreate('vendor_employee', 'web');
                $user->assignRole('vendor_employee');
                $data['user_id'] = $user->id;
            }

            $vendorUser = $this->vendorUserRepository->create([
                'vendor_id' => $vendorId,
                'user_id' => $data['user_id'],
                'is_active' => $data['is_active'] ?? true,
                'user_type' => $data['user_type'] ?? 'owner',
                'branch_id' => $data['branch_id'] ?? null,
            ]);

            $this->syncVendorPermissions($vendorUser->user, $data['permissions'] ?? [], $actor);

            return $vendorUser->load(['user.permissions', 'vendor', 'branch']);
        });
    }

    /**
     * @param  array{name?: string, email?: ?string, phone?: ?string, password?: ?string, is_active?: bool, permissions?: array<int, string>, user_type?: string, branch_id?: ?int}  $data
     */
    public function updateVendorUser(VendorUser $vendorUser, array $data, ?User $actor = null): VendorUser
    {
        return DB::transaction(function () use ($vendorUser, $data, $actor): VendorUser {
            $userData = [];

            if (array_key_exists('name', $data)) {
                $userData['name'] = $data['name'];
            }
            if (array_key_exists('email', $data)) {
                $userData['email'] = $data['email'];
            }
            if (array_key_exists('phone', $data)) {
                $userData['phone'] = $data['phone'];
            }
            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            if ($userData !== []) {
                $vendorUser->user->update($userData);
            }

            $vendorUserData = [];
            if (array_key_exists('is_active', $data)) {
                $vendorUserData['is_active'] = $data['is_active'];
            }
            if (array_key_exists('user_type', $data)) {
                $vendorUserData['user_type'] = $data['user_type'];
            }
            if (array_key_exists('branch_id', $data)) {
                $vendorUserData['branch_id'] = $data['branch_id'];
            }

            if ($vendorUserData !== []) {
                $this->vendorUserRepository->update($vendorUser, $vendorUserData);
            }

            if (array_key_exists('permissions', $data)) {
                $this->syncVendorPermissions($vendorUser->user, $data['permissions'] ?? [], $actor);
            }

            return $vendorUser->fresh(['user.permissions', 'vendor', 'branch']);
        });
    }

    public function deleteVendorUser(VendorUser $vendorUser): bool
    {
        return DB::transaction(function () use ($vendorUser): bool {
            $user = $vendorUser->user;
            $deleted = $this->vendorUserRepository->delete($vendorUser);

            if ($deleted && $user && $user->hasRole('vendor_employee')) {
                $user->syncPermissions([]);
            }

            return $deleted;
        });
    }

    public function toggleActive(VendorUser $vendorUser): VendorUser
    {
        return $this->vendorUserRepository->toggleActive($vendorUser);
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    protected function syncVendorPermissions(User $user, array $permissionNames, ?User $actor = null): void
    {
        $allowedNames = $this->assignablePermissions($actor)->pluck('name')->all();
        $filtered = array_values(array_intersect($permissionNames, $allowedNames));

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $filtered)
            ->get();

        $user->syncPermissions($permissions);
    }
}
