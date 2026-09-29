<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorUser;
use App\Services\VendorUserService;
use Database\Seeders\AdminPermissionsSeeder;
use Database\Seeders\VendorPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(VendorPermissionsSeeder::class);
        $this->seed(AdminPermissionsSeeder::class);

        foreach (['vendor', 'vendor_employee', 'admin'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    public function test_vendor_owner_can_create_employee_with_vendor_permissions_only(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $owner->assignRole('vendor');
        $vendor = Vendor::factory()->withOwner($owner->id)->create();

        $response = $this->actingAs($owner)->post(route('vendor.vendor-users.store'), [
            'name' => 'Shop Manager',
            'email' => 'manager@example.com',
            'phone' => '01011112222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'user_type' => 'owner',
            'permissions' => ['view-dashboard', 'view-products', 'manage-admin-orders'],
        ]);

        $response->assertSessionHasErrors('permissions.2');
        $this->assertDatabaseMissing('users', ['email' => 'manager@example.com']);

        $this->actingAs($owner)->post(route('vendor.vendor-users.store'), [
            'name' => 'Shop Manager',
            'email' => 'manager@example.com',
            'phone' => '01011112222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'user_type' => 'owner',
            'permissions' => ['view-dashboard', 'view-products'],
        ])->assertRedirect(route('vendor.vendor-users.index'));

        $employee = User::query()->where('email', 'manager@example.com')->firstOrFail();
        $this->assertTrue($employee->hasRole('vendor_employee'));
        $this->assertTrue($employee->hasAllDirectPermissions(['view-dashboard', 'view-products']));
        $this->assertDatabaseHas('vendor_users', [
            'vendor_id' => $vendor->id,
            'user_id' => $employee->id,
            'is_active' => true,
        ]);
    }

    public function test_employee_with_manage_permission_can_access_vendor_users(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $owner->assignRole('vendor');
        $vendor = Vendor::factory()->withOwner($owner->id)->create();

        /** @var User $manager */
        $manager = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $manager->assignRole('vendor_employee');
        $manager->givePermissionTo(['manage-vendor-users', 'view-vendor-users', 'create-vendor-users', 'view-dashboard']);

        VendorUser::query()->create([
            'vendor_id' => $vendor->id,
            'user_id' => $manager->id,
            'is_active' => true,
            'user_type' => 'owner',
        ]);

        $this->actingAs($manager)
            ->get(route('vendor.vendor-users.index'))
            ->assertOk();

        $this->actingAs($manager)->post(route('vendor.vendor-users.store'), [
            'name' => 'Cashier',
            'email' => 'cashier@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'user_type' => 'owner',
            'permissions' => ['view-dashboard', 'manage-products'],
        ])->assertSessionHasErrors('permissions.1');

        $this->actingAs($manager)->post(route('vendor.vendor-users.store'), [
            'name' => 'Cashier',
            'email' => 'cashier@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'user_type' => 'owner',
            'permissions' => ['view-dashboard'],
        ])->assertRedirect(route('vendor.vendor-users.index'));

        $this->assertDatabaseHas('users', ['email' => 'cashier@example.com']);
    }

    public function test_employee_without_permission_cannot_view_vendor_users(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $owner->assignRole('vendor');
        $vendor = Vendor::factory()->withOwner($owner->id)->create();

        /** @var User $employee */
        $employee = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $employee->assignRole('vendor_employee');
        $employee->givePermissionTo('view-dashboard');

        VendorUser::query()->create([
            'vendor_id' => $vendor->id,
            'user_id' => $employee->id,
            'is_active' => true,
            'user_type' => 'owner',
        ]);

        $this->actingAs($employee)
            ->get(route('vendor.vendor-users.index'))
            ->assertForbidden();
    }

    public function test_view_only_employee_cannot_create_or_delete_vendor_users(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $owner->assignRole('vendor');
        $vendor = Vendor::factory()->withOwner($owner->id)->create();

        /** @var User $viewer */
        $viewer = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $viewer->assignRole('vendor_employee');
        $viewer->givePermissionTo(['view-vendor-users', 'view-dashboard']);

        VendorUser::query()->create([
            'vendor_id' => $vendor->id,
            'user_id' => $viewer->id,
            'is_active' => true,
            'user_type' => 'owner',
        ]);

        /** @var User $targetUser */
        $targetUser = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $targetUser->assignRole('vendor_employee');
        $target = VendorUser::query()->create([
            'vendor_id' => $vendor->id,
            'user_id' => $targetUser->id,
            'is_active' => true,
            'user_type' => 'owner',
        ]);

        $this->actingAs($viewer)
            ->get(route('vendor.vendor-users.index'))
            ->assertOk();

        $this->actingAs($viewer)
            ->get(route('vendor.vendor-users.create'))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->delete(route('vendor.vendor-users.destroy', $target))
            ->assertForbidden();
    }

    public function test_vendor_cannot_manage_another_vendors_users(): void
    {
        /** @var User $ownerA */
        $ownerA = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $ownerA->assignRole('vendor');
        $vendorA = Vendor::factory()->withOwner($ownerA->id)->create();

        /** @var User $ownerB */
        $ownerB = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $ownerB->assignRole('vendor');
        $vendorB = Vendor::factory()->withOwner($ownerB->id)->create();

        /** @var User $employee */
        $employee = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $employee->assignRole('vendor_employee');
        $vendorUser = VendorUser::query()->create([
            'vendor_id' => $vendorB->id,
            'user_id' => $employee->id,
            'is_active' => true,
            'user_type' => 'owner',
        ]);

        $this->actingAs($ownerA)
            ->get(route('vendor.vendor-users.edit', $vendorUser))
            ->assertForbidden();
    }

    public function test_all_vendor_permissions_are_seeded_and_visible_to_owner(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $owner->assignRole('vendor');
        Vendor::factory()->withOwner($owner->id)->create();

        foreach (VendorUserService::permissionNames() as $permission) {
            $this->assertDatabaseHas('permissions', [
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->actingAs($owner)
            ->get(route('vendor.vendor-users.create'))
            ->assertOk()
            ->assertSee(__('View Orders'))
            ->assertSee(__('Manage Vendor Settings'))
            ->assertSee(__('View Reports'))
            ->assertSee(__('Manage Tickets'));
    }

    public function test_vendor_module_permissions_are_enforced_independently(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $owner->assignRole('vendor');
        $vendor = Vendor::factory()->withOwner($owner->id)->create();

        /** @var User $employee */
        $employee = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $employee->assignRole('vendor_employee');
        $employee->givePermissionTo(['view-orders', 'view-products']);
        VendorUser::query()->create([
            'vendor_id' => $vendor->id,
            'user_id' => $employee->id,
            'is_active' => true,
            'user_type' => 'owner',
        ]);

        $this->actingAs($employee)
            ->get(route('vendor.orders.index'))
            ->assertOk();

        $this->actingAs($employee)
            ->get(route('vendor.products.create'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('vendor.customers.index'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('vendor.settings.index'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('vendor.reports.index'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('vendor.tickets.index'))
            ->assertForbidden();
    }

    public function test_report_permissions_do_not_grant_other_report_pages(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $owner->assignRole('vendor');
        $vendor = Vendor::factory()->withOwner($owner->id)->create();

        /** @var User $employee */
        $employee = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $employee->assignRole('vendor_employee');
        $employee->givePermissionTo('view-earnings-reports');
        VendorUser::query()->create([
            'vendor_id' => $vendor->id,
            'user_id' => $employee->id,
            'is_active' => true,
            'user_type' => 'owner',
        ]);

        $this->actingAs($employee)
            ->get(route('vendor.reports.index'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('vendor.reports.product-performance'))
            ->assertForbidden();

        $earningsResponse = $this->actingAs($employee)
            ->get(route('vendor.reports.earnings'));

        $this->assertNotSame(403, $earningsResponse->getStatusCode());
    }

    public function test_employee_is_redirected_to_first_permitted_vendor_page_after_login(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create(['is_active' => true, 'role' => 'vendor']);
        $owner->assignRole('vendor');
        $vendor = Vendor::factory()->withOwner($owner->id)->create();

        /** @var User $employee */
        $employee = User::factory()->create([
            'email' => 'orders-employee@example.com',
            'password' => 'password123',
            'is_active' => true,
            'role' => 'vendor',
        ]);
        $employee->assignRole('vendor_employee');
        $employee->givePermissionTo('view-orders');
        VendorUser::query()->create([
            'vendor_id' => $vendor->id,
            'user_id' => $employee->id,
            'is_active' => true,
            'user_type' => 'owner',
        ]);

        $this->post(route('login'), [
            'email' => 'orders-employee@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('vendor.orders.index'));
    }
}
