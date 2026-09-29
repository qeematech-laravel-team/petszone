<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customers\AdjustPointsRequest;
use App\Http\Requests\Admin\Customers\SendNotificationRequest;
use App\Http\Requests\Admin\Customers\SetPasswordRequest;
use App\Http\Requests\Admin\Customers\UpdateProfileRequest;
use App\Mail\ForgetPasswordMail;
use App\Models\User;
use App\Notifications\AdminManualNotification;
use App\Services\CustomerService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Display a listing of customers.
     */
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $search = trim((string) $request->get('search', ''));

        $filters = [
            'search' => $search,
            'status' => (string) $request->get('status', ''),
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
            'min_orders_count' => $request->get('min_orders_count', ''),
            'max_orders_count' => $request->get('max_orders_count', ''),
            'min_orders_total' => $request->get('min_orders_total', ''),
            'max_orders_total' => $request->get('max_orders_total', ''),
            'sort' => (string) $request->get('sort', ''),
        ];

        $customers = $this->customerService->getPaginatedCustomers($perPage, $filters);

        return view('admin.customers.index', compact('customers', 'search', 'filters'));
    }

    /**
     * Display the specified customer details.
     */
    public function show(User $user): View
    {
        [$customer, $orders] = $this->customerService->getCustomerWithOrders($user->id, 15);

        return view('admin.customers.show', [
            'customer' => $customer ?? $user,
            'orders' => $orders,
        ]);
    }

    public function edit(User $user): View
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        return view('admin.customers.edit', [
            'customer' => $user,
        ]);
    }

    public function update(UpdateProfileRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== 'user') {
            return back()->with('error', __('This action is only allowed for customers.'));
        }

        $customer = $this->customerService->updateCustomerProfile($user->id, $request->validated());

        if (! $customer) {
            return back()->with('error', __('Unable to update customer profile.'));
        }

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', __('Customer profile updated successfully.'));
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->role !== 'user') {
            return back()->with('error', __('This action is only allowed for customers.'));
        }

        $customer = $this->customerService->toggleCustomerActive($user->id);

        if (! $customer) {
            return back()->with('error', __('Unable to update customer status.'));
        }

        return back()->with(
            'success',
            $customer->is_active
                ? __('Customer has been activated successfully.')
                : __('Customer has been blocked successfully.')
        );
    }

    public function setPassword(SetPasswordRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== 'user') {
            return back()->with('error', __('This action is only allowed for customers.'));
        }

        $customer = $this->customerService->setCustomerPassword($user->id, (string) $request->validated('password'));

        if (! $customer) {
            return back()->with('error', __('Unable to update customer password.'));
        }

        return back()->with('success', __('Customer password updated successfully.'));
    }

    public function sendResetLink(User $user): RedirectResponse
    {
        if ($user->role !== 'user') {
            return back()->with('error', __('This action is only allowed for customers.'));
        }

        if (! $user->email) {
            return back()->with('error', __('This customer does not have an email address.'));
        }

        $code = random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email, 'phone' => $user->phone],
            [
                'token' => (string) $code,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(10),
            ]
        );

        Mail::to($user->email)->send(new ForgetPasswordMail($code));

        return back()->with('success', __('Password reset code sent successfully.'));
    }

    public function adjustPoints(AdjustPointsRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== 'user') {
            return back()->with('error', __('This action is only allowed for customers.'));
        }

        $data = $request->validated();
        $customer = $this->customerService->adjustCustomerPoints(
            $user->id,
            (string) $data['type'],
            (int) $data['amount'],
            $data['notes'] ?? null
        );

        if (! $customer) {
            return back()->with('error', __('Unable to update customer points.'));
        }

        return back()->with('success', __('Customer points updated successfully.'));
    }

    public function notify(SendNotificationRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== 'user') {
            return back()->with('error', __('This action is only allowed for customers.'));
        }

        $data = $request->validated();

        $this->notificationService->notifyUser(
            $user,
            new AdminManualNotification(
                (string) $data['title'],
                (string) $data['message'],
                (int) $request->user()->id
            )
        );

        return back()->with('success', __('Notification sent successfully.'));
    }
}
