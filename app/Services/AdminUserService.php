<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserService
{
    /**
     * @var array<string, string>
     */
    public const PERMISSIONS = [
        'view-admin-dashboard' => 'Dashboard',
        'manage-admin-catalog' => 'Catalog & Products',
        'manage-admin-content' => 'Content & Promotions',
        'manage-admin-vendors' => 'Vendors & Branches',
        'manage-admin-customers' => 'Customers',
        'manage-admin-orders' => 'Orders & Refunds',
        'manage-admin-finance' => 'Finance & Withdrawals',
        'manage-admin-subscriptions' => 'Plans & Subscriptions',
        'manage-admin-requests' => 'Vendor Requests',
        'manage-admin-support' => 'Tickets & Support',
        'manage-admin-reports' => 'Reports & Analytics',
        'manage-admin-settings' => 'System Settings',
    ];

    public function paginate(): LengthAwarePaginator
    {
        return User::role('admin_employee')
            ->with('permissions')
            ->latest()
            ->paginate(15);
    }

    /**
     * @return array<string, string>
     */
    public function availablePermissions(): array
    {
        return Permission::query()
            ->whereIn('name', array_keys(self::PERMISSIONS))
            ->get()
            ->sortBy(fn (Permission $permission): int => array_search($permission->name, array_keys(self::PERMISSIONS), true))
            ->mapWithKeys(fn (Permission $permission): array => [
                $permission->name => self::PERMISSIONS[$permission->name],
            ])
            ->all();
    }

    /**
     * @param  array{name: string, email: string, phone?: ?string, password: string, is_active?: bool, permissions?: array<int, string>}  $data
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => 'admin',
                'is_active' => $data['is_active'] ?? false,
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            Role::findOrCreate('admin_employee', 'web');
            $user->assignRole('admin_employee');
            $user->syncPermissions($data['permissions'] ?? []);

            return $user->load('permissions');
        });
    }

    /**
     * @param  array{name: string, email: string, phone?: ?string, password?: ?string, is_active?: bool, permissions?: array<int, string>}  $data
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? false,
            ];

            if (! empty($data['password'])) {
                $attributes['password'] = $data['password'];
            }

            $user->update($attributes);
            $user->syncPermissions($data['permissions'] ?? []);

            return $user->load('permissions');
        });
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->syncPermissions([]);
            $user->syncRoles([]);
            $user->newQuery()->whereKey($user->getKey())->delete();
        });
    }
}
