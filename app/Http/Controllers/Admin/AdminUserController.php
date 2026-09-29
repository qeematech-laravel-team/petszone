<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUsers\StoreAdminUserRequest;
use App\Http\Requests\Admin\AdminUsers\UpdateAdminUserRequest;
use App\Models\User;
use App\Services\AdminUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminUserService $adminUserService) {}

    public function index(): View
    {
        return view('admin.admin-users.index', [
            'adminUsers' => $this->adminUserService->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('admin.admin-users.create', [
            'permissions' => $this->adminUserService->availablePermissions(),
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $this->adminUserService->create($request->validated());

        return redirect()
            ->route('admin.admin-users.index')
            ->with('success', __('Admin employee created successfully.'));
    }

    public function show(User $adminUser): View
    {
        $this->ensureAdminEmployee($adminUser);

        return view('admin.admin-users.show', [
            'adminUser' => $adminUser->load('permissions'),
        ]);
    }

    public function edit(User $adminUser): View
    {
        $this->ensureAdminEmployee($adminUser);

        return view('admin.admin-users.edit', [
            'adminUser' => $adminUser->load('permissions'),
            'permissions' => $this->adminUserService->availablePermissions(),
        ]);
    }

    public function update(UpdateAdminUserRequest $request, User $adminUser): RedirectResponse
    {
        $this->adminUserService->update($adminUser, $request->validated());

        return redirect()
            ->route('admin.admin-users.index')
            ->with('success', __('Admin employee updated successfully.'));
    }

    public function destroy(User $adminUser): RedirectResponse
    {
        $this->ensureAdminEmployee($adminUser);
        $this->adminUserService->delete($adminUser);

        return redirect()
            ->route('admin.admin-users.index')
            ->with('success', __('Admin employee deleted successfully.'));
    }

    public function toggleActive(User $adminUser): RedirectResponse
    {
        $this->ensureAdminEmployee($adminUser);
        $adminUser->update(['is_active' => ! $adminUser->is_active]);

        return back()->with('success', __('Admin employee status updated successfully.'));
    }

    private function ensureAdminEmployee(User $user): void
    {
        abort_unless($user->hasRole('admin_employee'), 404);
    }
}
