<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProductRatingController;
use App\Http\Controllers\Admin\ProductReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\VendorRatingController;
use App\Http\Controllers\Admin\VendorReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryRequestController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductRatingController as VendorProductRatingController;
use App\Http\Controllers\Vendor\ProductReportController as VendorProductReportController;
use App\Http\Controllers\Vendor\SubscriptionController as VendorSubscriptionController;
use App\Http\Controllers\Vendor\VendorController as VendorVendorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('test-fb', [NotificationController::class, 'test']);
Route::get('send-sms', [NotificationController::class, 'sendSMS']);
Route::get('check-balance', [NotificationController::class, 'checkBalance']);

Route::group(['middleware' => 'locale'], function () {

    // Public landing pages
    Route::get('/', [LandingController::class, 'index'])->name('dashboard');
    Route::get('/features', [LandingController::class, 'features'])->name('landing.features');
    Route::get('/pricing', [LandingController::class, 'pricing'])->name('landing.pricing');

    Route::group(['middleware' => 'auth'], function () {

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');

        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Admin Routes
        Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['role:admin|admin_employee', 'admin.access']], function () {
            // Admin Dashboard
            Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

            // Admin Team
            Route::post('admin-users/{admin_user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('admin-users.toggle-active');
            Route::resource('admin-users', AdminUserController::class);

            // Settings Routes
            Route::get('/settings', [SettingController::class, 'index'])->name('settings');
            Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

            // Categories Routes
            Route::get('categories/export', [CategoryController::class, 'export'])->name('categories.export');
            Route::get('categories/import', [CategoryController::class, 'showImport'])->name('categories.import');
            Route::post('categories/import', [CategoryController::class, 'import'])->name('categories.import.store');
            Route::get('categories/import/template', [CategoryController::class, 'downloadTemplate'])->name('categories.import.template');
            Route::resource('categories', CategoryController::class);

            // Plans Routes
            Route::resource('plans', PlanController::class);

            // Vendors Routes
            Route::resource('vendors', VendorController::class);

            // Customers Routes
            Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/{user}', [CustomerController::class, 'show'])->name('customers.show');
            Route::post('customers/{user}/toggle-active', [CustomerController::class, 'toggleActive'])->name('customers.toggle-active');
            Route::get('customers/{user}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('customers/{user}', [CustomerController::class, 'update'])->name('customers.update');
            Route::post('customers/{user}/set-password', [CustomerController::class, 'setPassword'])->name('customers.set-password');
            Route::post('customers/{user}/send-reset-link', [CustomerController::class, 'sendResetLink'])->name('customers.send-reset-link');
            Route::post('customers/{user}/adjust-points', [CustomerController::class, 'adjustPoints'])->name('customers.adjust-points');
            Route::post('customers/{user}/notify', [CustomerController::class, 'notify'])->name('customers.notify');

            // Variants Routes
            Route::get('variants/export', [VariantController::class, 'export'])->name('variants.export');
            Route::get('variants/import', [VariantController::class, 'showImport'])->name('variants.import');
            Route::post('variants/import', [VariantController::class, 'import'])->name('variants.import.store');
            Route::get('variants/import/template', [VariantController::class, 'downloadTemplate'])->name('variants.import.template');
            Route::resource('variants', VariantController::class);
            Route::post('variants/{variant}/toggle-active', [VariantController::class, 'toggleActive'])->name('variants.toggle-active');
            Route::post('variants/{variant}/toggle-required', [VariantController::class, 'toggleRequired'])->name('variants.toggle-required');

            // Products Routes
            Route::get('products/export', [\App\Http\Controllers\Admin\ProductController::class, 'export'])->name('products.export');
            Route::get('products/import', [\App\Http\Controllers\Admin\ProductController::class, 'showImport'])->name('products.import');
            Route::post('products/import', [\App\Http\Controllers\Admin\ProductController::class, 'import'])->name('products.import.store');
            Route::get('products/import/template', [\App\Http\Controllers\Admin\ProductController::class, 'downloadTemplate'])->name('products.import.template');
            Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
            Route::post('products/{product}/toggle-active', [\App\Http\Controllers\Admin\ProductController::class, 'toggleActive'])->name('products.toggle-active');
            Route::post('products/{product}/toggle-featured', [\App\Http\Controllers\Admin\ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
            Route::post('products/{product}/toggle-approved', [\App\Http\Controllers\Admin\ProductController::class, 'toggleApproved'])->name('products.toggle-approved');

            // Branches Routes
            Route::resource('branches', \App\Http\Controllers\Admin\BranchController::class);
            Route::post('branches/{branch}/toggle-active', [\App\Http\Controllers\Admin\BranchController::class, 'toggleActive'])->name('branches.toggle-active');
            Route::get('branches/by-vendor/{vendorId}', [\App\Http\Controllers\Admin\BranchController::class, 'getBranchesByVendor'])->name('branches.by-vendor');

            // Sliders Routes
            Route::resource('sliders', \App\Http\Controllers\Admin\SliderController::class);

            // Coupons Routes
            Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class);

            // Variant Requests Routes
            Route::get('/variant-requests', [\App\Http\Controllers\VariantRequestController::class, 'index'])->name('variant-requests.index');
            Route::post('/variant-requests/{variantRequest}/approve', action: [\App\Http\Controllers\VariantRequestController::class, 'approve'])->name('variant-requests.approve');
            Route::post('/variant-requests/{variantRequest}/reject', [\App\Http\Controllers\VariantRequestController::class, 'reject'])->name('variant-requests.reject');

            // Category Requests Routes
            Route::get('/category-requests', [CategoryRequestController::class, 'index'])->name('category-requests.index');
            Route::post('/category-requests/{categoryRequest}/approve', [CategoryRequestController::class, 'approve'])->name('category-requests.approve');
            Route::post('/category-requests/{categoryRequest}/reject', [CategoryRequestController::class, 'reject'])->name('category-requests.reject');

            // Subscriptions Routes
            Route::resource('subscriptions', SubscriptionController::class)->only(['index', 'show']);

            // Tickets Routes
            Route::resource('tickets', \App\Http\Controllers\Admin\TicketController::class);
            Route::post('tickets/{ticket}/add-message', [\App\Http\Controllers\Admin\TicketController::class, 'addMessage'])->name('tickets.add-message');
            Route::post('tickets/{ticket}/update-status', [\App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('tickets.update-status');

            // Orders Routes
            Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)->only(['index', 'show', 'update', 'destroy']);
            Route::post('orders/{order}/update-status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::post('orders/{order}/refund', [\App\Http\Controllers\Admin\OrderController::class, 'refund'])->name('orders.refund');
            Route::get('orders/{order}/invoice', [\App\Http\Controllers\Admin\OrderController::class, 'invoice'])->name('orders.invoice');

            // Reports & Analytics
            Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/earnings', [\App\Http\Controllers\Admin\ReportController::class, 'earnings'])->name('reports.earnings');
            Route::get('reports/product-performance', [\App\Http\Controllers\Admin\ReportController::class, 'productPerformance'])->name('reports.product-performance');
            Route::get('reports/vendor-performance', [\App\Http\Controllers\Admin\ReportController::class, 'vendorPerformance'])->name('reports.vendor-performance');

            // Order Refund Requests
            Route::get('order-refund-requests', [\App\Http\Controllers\Admin\OrderRefundRequestController::class, 'index'])->name('order-refund-requests.index');
            Route::post('order-refund-requests/{orderRefundRequest}/approve', [\App\Http\Controllers\Admin\OrderRefundRequestController::class, 'approve'])->name('order-refund-requests.approve');
            Route::post('order-refund-requests/{orderRefundRequest}/reject', [\App\Http\Controllers\Admin\OrderRefundRequestController::class, 'reject'])->name('order-refund-requests.reject');

            // Vendor Withdrawals
            Route::get('vendor-withdrawals', [\App\Http\Controllers\Admin\VendorWithdrawalController::class, 'index'])->name('vendor-withdrawals.index');
            Route::post('vendor-withdrawals/{vendorWithdrawal}/approve', [\App\Http\Controllers\Admin\VendorWithdrawalController::class, 'approve'])->name('vendor-withdrawals.approve');
            Route::post('vendor-withdrawals/{vendorWithdrawal}/reject', [\App\Http\Controllers\Admin\VendorWithdrawalController::class, 'reject'])->name('vendor-withdrawals.reject');

            // Ratings & Reports (admin views)
            Route::get('product-ratings', [ProductRatingController::class, 'index'])->name('product-ratings.index');
            Route::post('product-ratings/{productRating}/toggle-visibility', [ProductRatingController::class, 'toggleVisibility'])->name('product-ratings.toggle-visibility');
            Route::get('product-reports', [ProductReportController::class, 'index'])->name('product-reports.index');
            Route::post('product-reports/{productReport}/status/{status}', [ProductReportController::class, 'updateStatus'])->name('product-reports.update-status');
            Route::get('vendor-ratings', [VendorRatingController::class, 'index'])->name('vendor-ratings.index');
            Route::post('vendor-ratings/{vendorRating}/toggle-visibility', [VendorRatingController::class, 'toggleVisibility'])->name('vendor-ratings.toggle-visibility');
            Route::get('vendor-reports', [VendorReportController::class, 'index'])->name('vendor-reports.index');
            Route::post('vendor-reports/{vendorReport}/status/{status}', [VendorReportController::class, 'updateStatus'])->name('vendor-reports.update-status');

        });

        // Vendor Routes
        Route::group(['prefix' => 'vendor', 'as' => 'vendor.', 'middleware' => 'vendor.user'], function () {

            // Vendor Profile (requires edit-profile permission)
            Route::get('/profile', [VendorVendorController::class, 'edit'])->name('profile')->middleware('role_or_permission:vendor|edit-profile');
            Route::put('/profile', [VendorVendorController::class, 'update'])->name('profile.update')->middleware('role_or_permission:vendor|edit-profile');

            // Vendor Dashboard (requires view-dashboard permission)
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('role_or_permission:vendor|view-dashboard');

            // Branch Dashboard (for branch users only)
            Route::get('/branch/dashboard', [\App\Http\Controllers\Vendor\BranchDashboardController::class, 'index'])
                ->name('branch.dashboard')
                ->middleware('role_or_permission:vendor|view-branch-dashboard');

            // Categories (read-only for vendors - requires view-categories permission)
            Route::get('/categories', [\App\Http\Controllers\Vendor\CategoryController::class, 'index'])->name('categories.index')->middleware('role_or_permission:vendor|view-categories');

            // Variants (read-only for vendors - requires view-variants permission)
            Route::get('/variants', [\App\Http\Controllers\Vendor\VariantController::class, 'index'])->name('variants.index')->middleware('role_or_permission:vendor|view-variants');

            // Branches Routes
            Route::resource('branches', \App\Http\Controllers\Vendor\BranchController::class)
                ->only(['index'])
                ->middleware('role_or_permission:vendor|manage-branches|view-branches');
            Route::resource('branches', \App\Http\Controllers\Vendor\BranchController::class)
                ->only(['create', 'store'])
                ->middleware('role_or_permission:vendor|manage-branches|create-branches');
            Route::resource('branches', \App\Http\Controllers\Vendor\BranchController::class)
                ->only(['show'])
                ->middleware('role_or_permission:vendor|manage-branches|view-branches');
            Route::resource('branches', \App\Http\Controllers\Vendor\BranchController::class)
                ->only(['edit', 'update'])
                ->middleware('role_or_permission:vendor|manage-branches|edit-branches');
            Route::resource('branches', \App\Http\Controllers\Vendor\BranchController::class)
                ->only(['destroy'])
                ->middleware('role_or_permission:vendor|manage-branches|delete-branches');
            Route::post('branches/{branch}/toggle-active', [\App\Http\Controllers\Vendor\BranchController::class, 'toggleActive'])->name('branches.toggle-active')->middleware('role_or_permission:vendor|manage-branches|edit-branches');

            // Products Routes
            Route::resource('products', \App\Http\Controllers\Vendor\ProductController::class)
                ->only(['index'])
                ->middleware('role_or_permission:vendor|manage-products|view-products');
            Route::resource('products', \App\Http\Controllers\Vendor\ProductController::class)
                ->only(['create', 'store'])
                ->middleware('role_or_permission:vendor|manage-products|create-products');
            Route::resource('products', \App\Http\Controllers\Vendor\ProductController::class)
                ->only(['show'])
                ->middleware('role_or_permission:vendor|manage-products|view-products');
            Route::resource('products', \App\Http\Controllers\Vendor\ProductController::class)
                ->only(['edit', 'update'])
                ->middleware('role_or_permission:vendor|manage-products|edit-products');
            Route::resource('products', \App\Http\Controllers\Vendor\ProductController::class)
                ->only(['destroy'])
                ->middleware('role_or_permission:vendor|manage-products|delete-products');
            Route::post('products/{product}/toggle-active', [\App\Http\Controllers\Vendor\ProductController::class, 'toggleActive'])->name('products.toggle-active')->middleware('role_or_permission:vendor|view-products|manage-products|edit-products');
            Route::post('products/{product}/toggle-featured', [\App\Http\Controllers\Vendor\ProductController::class, 'toggleFeatured'])->name('products.toggle-featured')->middleware('role_or_permission:vendor|manage-products|edit-products');
            Route::post('products/{product}/update-branch-stock', [\App\Http\Controllers\Vendor\ProductController::class, 'updateBranchStock'])->name('products.update-branch-stock')->middleware('role_or_permission:vendor|manage-products|manage-product-stock');
            Route::post('products/{product}/update-branch-price', [\App\Http\Controllers\Vendor\ProductController::class, 'updateBranchPrice'])->name('products.update-branch-price')->middleware('role_or_permission:vendor|manage-products|manage-product-prices');
            Route::get('products/branches/by-vendor', [\App\Http\Controllers\Vendor\ProductController::class, 'getBranchesByVendor'])->name('products.branches.by-vendor')->middleware('role_or_permission:vendor|manage-products|create-products|edit-products');

            // Variant Requests (requires create-variant-requests or view-variant-requests permission)
            Route::get('/variant-requests', [\App\Http\Controllers\VariantRequestController::class, 'vendorIndex'])->name('variant-requests.index')->middleware('role_or_permission:vendor|view-variant-requests');
            Route::post('/variant-requests', [\App\Http\Controllers\VariantRequestController::class, 'store'])->name('variant-requests.store')->middleware('role_or_permission:vendor|create-variant-requests');

            // Category Requests (requires create-category-requests or view-category-requests permission)
            Route::get('/category-requests', [CategoryRequestController::class, 'vendorIndex'])->name('category-requests.index')->middleware('role_or_permission:vendor|view-category-requests');
            Route::post('/category-requests', [CategoryRequestController::class, 'store'])->name('category-requests.store')->middleware('role_or_permission:vendor|create-category-requests');

            // Plans Requests (requires view-plans or subscribe-plans permission)
            Route::get('/plans', [PlanController::class, 'vendorIndex'])->name('plans.index')->middleware('role_or_permission:vendor|view-plans');
            Route::post('/plans/subscribe', [PlanController::class, 'subscribe'])->name('plans.subscribe')->middleware('role_or_permission:vendor|subscribe-plans');

            // Settings
            Route::get('/settings', [\App\Http\Controllers\Vendor\SettingController::class, 'index'])->name('settings.index')->middleware('role_or_permission:vendor|manage-settings');
            Route::put('/settings', [\App\Http\Controllers\Vendor\SettingController::class, 'update'])->name('settings.update')->middleware('role_or_permission:vendor|manage-settings');
            Route::post('/plans/check', [PlanController::class, 'check'])->name('plans.check')->middleware('role_or_permission:vendor|view-plans');

            // Subscriptions Routes (requires view-subscriptions or cancel-subscriptions permission)
            Route::group(['middleware' => 'role_or_permission:vendor|view-subscriptions'], function () {
                Route::resource('subscriptions', VendorSubscriptionController::class)->only(['index', 'show']);
            });
            Route::post('subscriptions/{subscription}/cancel', [VendorSubscriptionController::class, 'cancel'])->name('subscriptions.cancel')->middleware('role_or_permission:vendor|cancel-subscriptions');

            // Vendor Users Routes
            Route::get('vendor-users', [\App\Http\Controllers\Vendor\VendorUserController::class, 'index'])
                ->name('vendor-users.index')
                ->middleware('role_or_permission:vendor|manage-vendor-users|view-vendor-users');
            Route::get('vendor-users/create', [\App\Http\Controllers\Vendor\VendorUserController::class, 'create'])
                ->name('vendor-users.create')
                ->middleware('role_or_permission:vendor|manage-vendor-users|create-vendor-users');
            Route::post('vendor-users', [\App\Http\Controllers\Vendor\VendorUserController::class, 'store'])
                ->name('vendor-users.store')
                ->middleware('role_or_permission:vendor|manage-vendor-users|create-vendor-users');
            Route::get('vendor-users/{vendor_user}', [\App\Http\Controllers\Vendor\VendorUserController::class, 'show'])
                ->name('vendor-users.show')
                ->middleware('role_or_permission:vendor|manage-vendor-users|view-vendor-users');
            Route::get('vendor-users/{vendor_user}/edit', [\App\Http\Controllers\Vendor\VendorUserController::class, 'edit'])
                ->name('vendor-users.edit')
                ->middleware('role_or_permission:vendor|manage-vendor-users|edit-vendor-users');
            Route::match(['put', 'patch'], 'vendor-users/{vendor_user}', [\App\Http\Controllers\Vendor\VendorUserController::class, 'update'])
                ->name('vendor-users.update')
                ->middleware('role_or_permission:vendor|manage-vendor-users|edit-vendor-users');
            Route::delete('vendor-users/{vendor_user}', [\App\Http\Controllers\Vendor\VendorUserController::class, 'destroy'])
                ->name('vendor-users.destroy')
                ->middleware('role_or_permission:vendor|manage-vendor-users|delete-vendor-users');
            Route::post('vendor-users/{vendor_user}/toggle-active', [\App\Http\Controllers\Vendor\VendorUserController::class, 'toggleActive'])
                ->name('vendor-users.toggle-active')
                ->middleware('role_or_permission:vendor|manage-vendor-users|edit-vendor-users');

            // Tickets Routes
            Route::resource('tickets', \App\Http\Controllers\Vendor\TicketController::class)
                ->only(['index'])
                ->middleware('role_or_permission:vendor|manage-tickets|view-tickets');
            Route::resource('tickets', \App\Http\Controllers\Vendor\TicketController::class)
                ->only(['create', 'store'])
                ->middleware('role_or_permission:vendor|manage-tickets|create-tickets');
            Route::resource('tickets', \App\Http\Controllers\Vendor\TicketController::class)
                ->only(['show'])
                ->middleware('role_or_permission:vendor|manage-tickets|view-tickets');
            Route::resource('tickets', \App\Http\Controllers\Vendor\TicketController::class)
                ->only(['update'])
                ->middleware('role_or_permission:vendor|manage-tickets|edit-tickets');
            Route::resource('tickets', \App\Http\Controllers\Vendor\TicketController::class)
                ->only(['destroy'])
                ->middleware('role_or_permission:vendor|manage-tickets|delete-tickets');
            Route::post('tickets/{ticket}/add-message', [\App\Http\Controllers\Vendor\TicketController::class, 'addMessage'])->name('tickets.add-message')->middleware('role_or_permission:vendor|manage-tickets|reply-tickets');
            Route::post('tickets/{ticket}/update-status', [\App\Http\Controllers\Vendor\TicketController::class, 'updateStatus'])->name('tickets.update-status')->middleware('role_or_permission:vendor|manage-tickets|update-ticket-status');

            // Orders Routes
            Route::resource('orders', \App\Http\Controllers\Vendor\OrderController::class)
                ->only(['index', 'show'])
                ->middleware('role_or_permission:vendor|view-orders');
            Route::resource('orders', \App\Http\Controllers\Vendor\OrderController::class)
                ->only(['update'])
                ->middleware('role_or_permission:vendor|edit-orders');
            Route::post('orders/{order}/update-status', [\App\Http\Controllers\Vendor\OrderController::class, 'updateStatus'])->name('orders.update-status')->middleware('role_or_permission:vendor|update-order-status');
            Route::get('orders/{order}/invoice', [VendorOrderController::class, 'invoice'])->name('orders.invoice')->middleware('role_or_permission:vendor|view-order-invoices');

            // Customers
            Route::get('customers', [\App\Http\Controllers\Vendor\CustomerController::class, 'index'])->name('customers.index')->middleware('role_or_permission:vendor|view-customers');
            Route::get('customers/{user}', [\App\Http\Controllers\Vendor\CustomerController::class, 'show'])->name('customers.show')->middleware('role_or_permission:vendor|view-customers');

            // Withdrawals
            Route::get('withdrawals', [\App\Http\Controllers\Vendor\WithdrawalController::class, 'index'])->name('withdrawals.index')->middleware('role_or_permission:vendor|view-withdrawals');
            Route::post('withdrawals', [\App\Http\Controllers\Vendor\WithdrawalController::class, 'store'])->name('withdrawals.store')->middleware('role_or_permission:vendor|create-withdrawals');

            // Reports & Analytics
            Route::get('reports', [\App\Http\Controllers\Vendor\ReportController::class, 'index'])->name('reports.index')->middleware('role_or_permission:vendor|view-reports');
            Route::get('reports/earnings', [\App\Http\Controllers\Vendor\ReportController::class, 'earnings'])->name('reports.earnings')->middleware('role_or_permission:vendor|view-earnings-reports');
            Route::get('reports/product-performance', [\App\Http\Controllers\Vendor\ReportController::class, 'productPerformance'])->name('reports.product-performance')->middleware('role_or_permission:vendor|view-product-performance-reports');
            Route::get('reports/vendor-performance', [\App\Http\Controllers\Vendor\ReportController::class, 'vendorPerformance'])->name('reports.vendor-performance')->middleware('role_or_permission:vendor|view-vendor-performance-reports');

            // Ratings & Reports
            Route::get('product-ratings', [VendorProductRatingController::class, 'index'])->name('product-ratings.index')->middleware('role_or_permission:vendor|view-product-ratings');
            Route::post('product-ratings/{productRating}/toggle-visibility', [VendorProductRatingController::class, 'toggleVisibility'])->name('product-ratings.toggle-visibility')->middleware('role_or_permission:vendor|manage-product-ratings');
            Route::get('product-reports', [VendorProductReportController::class, 'index'])->name('product-reports.index')->middleware('role_or_permission:vendor|view-product-reports');
            Route::post('product-reports/{productReport}/status/{status}', [VendorProductReportController::class, 'updateStatus'])->name('product-reports.update-status')->middleware('role_or_permission:vendor|manage-product-reports');

        });

    });

    // Locale/Language Routes
    Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

    // Vendor Registration Routes (Public)
    Route::get('/vendor/register', [VendorController::class, 'showRegistrationForm'])->name('vendor.register');
    Route::post('/vendor/register', [VendorController::class, 'register'])->name('vendor.register.submit');

    //  Test Routes
    Route::get('/analytics', function () {
        return view('analytics.analytics');
    })->name('analytics');

    Route::get('/users', function () {
        return view('users.users');
    })->name('users.index');

    Route::get('/products', function () {
        return view('products.products');
    })->name('products.index');

    Route::get('/orders', function () {
        return view('orders.orders');
    })->name('orders.index');

    Route::get('/forms', function () {
        return view('forms.forms');
    })->name('forms');

    Route::get('/elements', function () {
        return view('elements');
    })->name('elements.index');

    Route::get('/elements/buttons', function () {
        return view('elements.elements-buttons');
    })->name('elements.buttons');

    Route::get('/elements/alerts', function () {
        return view('elements.elements-alerts');
    })->name('elements.alerts');

    Route::get('/elements/badges', function () {
        return view('elements.elements-badges');
    })->name('elements.badges');

    Route::get('/elements/cards', function () {
        return view('elements.elements-cards');
    })->name('elements.cards');

    Route::get('/elements/modals', function () {
        return view('elements.elements-modals');
    })->name('elements.modals');

    Route::get('/elements/forms', function () {
        return view('elements.elements-forms');
    })->name('elements.forms');

    Route::get('/elements/tables', function () {
        return view('elements.elements-tables');
    })->name('elements.tables');

    Route::get('/reports', function () {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('admin') || $user->hasRole('admin_employee')) {
                return redirect()->route(
                    adminCan('manage-admin-reports') ? 'admin.reports.index' : adminHomeRoute()
                );
            }
            if ($user->hasRole('vendor') || $user->hasRole('vendor_employee')) {
                return redirect()->route('vendor.reports.index');
            }
        }

        return redirect()->route('dashboard');
    })->name('reports');

    Route::get('/messages', function () {
        return view('messages.messages');
    })->name('messages.index');

    Route::get('/calendar', function () {
        return view('calendar.calendar');
    })->name('calendar');

    Route::get('/files', function () {
        return view('files.files');
    })->name('files.index');

    Route::get('/security', function () {
        return view('security.security');
    })->name('security');

    Route::get('/help', function () {
        return view('help.help');
    })->name('help');

    require __DIR__.'/auth.php';

});
