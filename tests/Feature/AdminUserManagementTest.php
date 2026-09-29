<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminPermissionsSeeder::class);
        Role::findOrCreate('admin', 'web');
    }

    public function test_admin_can_create_employee_with_password_and_permissions(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.admin-users.store'), [
            'name' => 'Operations Employee',
            'email' => 'operations@example.com',
            'phone' => '01000000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'permissions' => ['view-admin-dashboard', 'manage-admin-orders'],
        ]);

        $response->assertRedirect(route('admin.admin-users.index'));

        $employee = User::query()->where('email', 'operations@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('password123', $employee->password));
        $this->assertTrue($employee->hasRole('admin_employee'));
        $this->assertTrue($employee->hasAllDirectPermissions([
            'view-admin-dashboard',
            'manage-admin-orders',
        ]));
    }

    public function test_employee_can_only_access_granted_admin_modules(): void
    {
        /** @var User $employee */
        $employee = User::factory()->create(['is_active' => true]);
        $employee->assignRole('admin_employee');
        $employee->givePermissionTo('view-admin-dashboard');

        $this->actingAs($employee)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($employee)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_employee_cannot_manage_admin_team(): void
    {
        /** @var User $employee */
        $employee = User::factory()->create(['is_active' => true]);
        $employee->assignRole('admin_employee');
        $employee->givePermissionTo('view-admin-dashboard');

        $this->actingAs($employee)
            ->get(route('admin.admin-users.index'))
            ->assertForbidden();
    }

    public function test_inactive_employee_cannot_log_in_or_use_existing_session(): void
    {
        /** @var User $employee */
        $employee = User::factory()->create([
            'email' => 'inactive-admin@example.com',
            'password' => 'password123',
            'is_active' => false,
        ]);
        $employee->assignRole('admin_employee');
        $employee->givePermissionTo('view-admin-dashboard');

        $this->post(route('login'), [
            'email' => 'inactive-admin@example.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->actingAs($employee)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_cannot_assign_non_admin_permissions(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.admin-users.store'), [
                'name' => 'Invalid Employee',
                'email' => 'invalid@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'is_active' => true,
                'permissions' => ['manage-vendor-users'],
            ])
            ->assertSessionHasErrors('permissions.0');

        $this->assertDatabaseMissing('users', ['email' => 'invalid@example.com']);
    }

    public function test_admin_can_update_toggle_and_delete_employee(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        /** @var User $employee */
        $employee = User::factory()->create(['is_active' => true]);
        $employee->assignRole('admin_employee');
        $employee->givePermissionTo('view-admin-dashboard');

        $this->actingAs($admin)
            ->post(route('admin.admin-users.toggle-active', $employee))
            ->assertRedirect();

        $this->assertFalse($employee->fresh()->is_active);

        $this->actingAs($admin)
            ->put(route('admin.admin-users.update', $employee), [
                'name' => 'Updated Employee',
                'email' => $employee->email,
                'phone' => null,
                'password' => '',
                'is_active' => true,
                'permissions' => ['manage-admin-customers'],
            ])
            ->assertRedirect(route('admin.admin-users.index'));

        $employee->refresh();
        $this->assertSame('Updated Employee', $employee->name);
        $this->assertTrue($employee->is_active);
        $this->assertTrue($employee->hasExactRoles('admin_employee'));
        $this->assertSame(
            ['manage-admin-customers'],
            $employee->getDirectPermissions()->pluck('name')->all()
        );

        $this->actingAs($admin)
            ->delete(route('admin.admin-users.destroy', $employee))
            ->assertRedirect(route('admin.admin-users.index'));

        $this->assertSoftDeleted($employee);
    }
}
