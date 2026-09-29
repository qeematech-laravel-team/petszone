<!-- Sidebar -->
<aside class="admin-sidebar" id="admin-sidebar" style="overflow-y: scroll">
    <div class="sidebar-content">
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                @auth
                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('admin_employee'))
                        <!-- Admin Menu -->
                        @if(adminCan('view-admin-dashboard'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2"></i>
                                    <span>{{ __('Dashboard') }}</span>
                                </a>
                            </li>
                        @endif

                        @php
                            $adminSections = [
                                'Catalog & Content' => [
                                    ['label' => 'Categories', 'route' => 'admin.categories.index', 'active' => 'admin.categories*', 'icon' => 'bi-grid', 'permission' => 'manage-admin-catalog'],
                                    ['label' => 'Variants', 'route' => 'admin.variants.index', 'active' => 'admin.variants*', 'icon' => 'bi-tags', 'permission' => 'manage-admin-catalog'],
                                    ['label' => 'Products', 'route' => 'admin.products.index', 'active' => 'admin.products*', 'icon' => 'bi-box-seam', 'permission' => 'manage-admin-catalog'],
                                    ['label' => 'Branches', 'route' => 'admin.branches.index', 'active' => 'admin.branches*', 'icon' => 'bi-shop', 'permission' => 'manage-admin-vendors'],
                                    ['label' => 'Sliders', 'route' => 'admin.sliders.index', 'active' => 'admin.sliders*', 'icon' => 'bi-images', 'permission' => 'manage-admin-content'],
                                    ['label' => 'Coupons', 'route' => 'admin.coupons.index', 'active' => 'admin.coupons*', 'icon' => 'bi-ticket-perforated', 'permission' => 'manage-admin-content'],
                                ],
                                'Vendors & Customers' => [
                                    ['label' => 'Vendors', 'route' => 'admin.vendors.index', 'active' => 'admin.vendors*', 'icon' => 'bi-people', 'permission' => 'manage-admin-vendors'],
                                    ['label' => 'Customers', 'route' => 'admin.customers.index', 'active' => 'admin.customers*', 'icon' => 'bi-people-fill', 'permission' => 'manage-admin-customers'],
                                ],
                                'Subscriptions & Requests' => [
                                    ['label' => 'Subscriptions', 'route' => 'admin.subscriptions.index', 'active' => 'admin.subscriptions*', 'icon' => 'bi-credit-card-2-front', 'permission' => 'manage-admin-subscriptions'],
                                    ['label' => 'Category Requests', 'route' => 'admin.category-requests.index', 'active' => 'admin.category-requests*', 'icon' => 'bi-inbox', 'permission' => 'manage-admin-requests'],
                                    ['label' => 'Variant Requests', 'route' => 'admin.variant-requests.index', 'active' => 'admin.variant-requests*', 'icon' => 'bi-inbox', 'permission' => 'manage-admin-requests'],
                                    ['label' => 'Tickets', 'route' => 'admin.tickets.index', 'active' => 'admin.tickets*', 'icon' => 'bi-headset', 'permission' => 'manage-admin-support'],
                                ],
                                'Orders & Finance' => [
                                    ['label' => 'Orders', 'route' => 'admin.orders.index', 'active' => 'admin.orders*', 'icon' => 'bi-cart-check', 'permission' => 'manage-admin-orders'],
                                    ['label' => 'Order Refund Requests', 'route' => 'admin.order-refund-requests.index', 'active' => 'admin.order-refund-requests*', 'icon' => 'bi-arrow-counterclockwise', 'permission' => 'manage-admin-orders'],
                                    ['label' => 'Vendor Withdrawals', 'route' => 'admin.vendor-withdrawals.index', 'active' => 'admin.vendor-withdrawals*', 'icon' => 'bi-cash-coin', 'permission' => 'manage-admin-finance'],
                                ],
                                'Ratings & Reports' => [
                                    ['label' => 'Product Ratings', 'route' => 'admin.product-ratings.index', 'active' => 'admin.product-ratings*', 'icon' => 'bi-star', 'permission' => 'manage-admin-catalog'],
                                    ['label' => 'Product Reports', 'route' => 'admin.product-reports.index', 'active' => 'admin.product-reports*', 'icon' => 'bi-flag', 'permission' => 'manage-admin-catalog'],
                                    ['label' => 'Vendor Ratings', 'route' => 'admin.vendor-ratings.index', 'active' => 'admin.vendor-ratings*', 'icon' => 'bi-star-half', 'permission' => 'manage-admin-vendors'],
                                    ['label' => 'Vendor Reports', 'route' => 'admin.vendor-reports.index', 'active' => 'admin.vendor-reports*', 'icon' => 'bi-flag-fill', 'permission' => 'manage-admin-vendors'],
                                    ['label' => 'Reports & Analytics', 'route' => 'admin.reports.index', 'active' => 'admin.reports*', 'icon' => 'bi-graph-up', 'permission' => 'manage-admin-reports'],
                                ],
                                'System' => [
                                    ['label' => 'Settings', 'route' => 'admin.settings', 'active' => 'admin.settings*', 'icon' => 'bi-gear', 'permission' => 'manage-admin-settings'],
                                ],
                            ];
                        @endphp

                        @foreach($adminSections as $section => $items)
                            @php
                                $visibleItems = collect($items)->filter(fn ($item) => adminCan($item['permission']));
                            @endphp
                            @if($visibleItems->isNotEmpty())
                                <li class="nav-item mt-3">
                                    <small class="text-muted px-3 text-uppercase fw-bold">{{ __($section) }}</small>
                                </li>
                                @foreach($visibleItems as $item)
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                                            <i class="bi {{ $item['icon'] }}"></i>
                                            <span>{{ __($item['label']) }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        @endforeach

                        @if(auth()->user()->hasRole('admin'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.admin-users*') ? 'active' : '' }}" href="{{ route('admin.admin-users.index') }}">
                                    <i class="bi bi-person-gear"></i>
                                    <span>{{ __('Admin Team') }}</span>
                                </a>
                            </li>
                        @endif

                    @elseif(auth()->user()->hasRole('vendor') || auth()->user()->hasRole('vendor_employee'))
                        <!-- Vendor Menu -->
                        @if(($isBranchUser ?? false) && vendorCan('view-branch-dashboard'))
                            <!-- Branch Dashboard -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.branch.dashboard') ? 'active' : '' }}" href="{{ route('vendor.branch.dashboard') }}">
                                    <i class="bi bi-speedometer2"></i>
                                    <span>{{ __('Branch Dashboard') }}</span>
                                </a>
                            </li>
                        @elseif(vendorCan('view-dashboard'))
                            <!-- Vendor Dashboard -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}" href="{{ route('vendor.dashboard') }}">
                                    <i class="bi bi-speedometer2"></i>
                                    <span>{{ __('Vendor Dashboard') }}</span>
                                </a>
                            </li>
                        @endif

                        @if(vendorCan('view-categories') || vendorCan('view-variants') || vendorCan('view-products') || vendorCan('manage-products') || vendorCan('view-branches') || vendorCan('manage-branches') || vendorCan('view-product-ratings') || vendorCan('manage-product-ratings') || vendorCan('view-product-reports') || vendorCan('manage-product-reports'))
                            <li class="nav-item mt-3">
                                <small class="text-muted px-3 text-uppercase fw-bold">{{ __('Catalog & Products') }}</small>
                            </li>
                        @endif
                        @if(vendorCan('view-categories'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.categories*') ? 'active' : '' }}" href="{{ route('vendor.categories.index') }}">
                                    <i class="bi bi-grid"></i>
                                    <span>{{ __('Categories') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-variants'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.variants*') ? 'active' : '' }}" href="{{ route('vendor.variants.index') }}">
                                    <i class="bi bi-tags"></i>
                                    <span>{{ __('Variants') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-branches') || vendorCan('manage-branches'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.branches*') ? 'active' : '' }}" href="{{ route('vendor.branches.index') }}">
                                    <i class="bi bi-shop"></i>
                                    <span>{{ __('My Branches') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-products') || vendorCan('manage-products'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.products*') ? 'active' : '' }}" href="{{ route('vendor.products.index') }}">
                                    <i class="bi bi-box-seam"></i>
                                    <span>{{ __('My Products') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-product-ratings') || vendorCan('manage-product-ratings'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.product-ratings*') ? 'active' : '' }}" href="{{ route('vendor.product-ratings.index') }}">
                                    <i class="bi bi-star"></i>
                                    <span>{{ __('Product Ratings') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-product-reports') || vendorCan('manage-product-reports'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.product-reports*') ? 'active' : '' }}" href="{{ route('vendor.product-reports.index') }}">
                                    <i class="bi bi-flag"></i>
                                    <span>{{ __('Product Reports') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-orders') || vendorCan('view-withdrawals') || vendorCan('view-plans') || vendorCan('view-subscriptions'))
                            <li class="nav-item mt-3">
                                <small class="text-muted px-3 text-uppercase fw-bold">{{ __('Sales & Finance') }}</small>
                            </li>
                        @endif
                        @if(vendorCan('view-orders'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.orders*') ? 'active' : '' }}" href="{{ route('vendor.orders.index') }}">
                                    <i class="bi bi-cart-check"></i>
                                    <span>{{ __('Orders') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-withdrawals'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.withdrawals*') ? 'active' : '' }}" href="{{ route('vendor.withdrawals.index') }}">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>{{ __('Withdrawals') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(setting('profit_type') == 'subscription' && vendorCan('view-plans'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.plans*') ? 'active' : '' }}" href="{{ route('vendor.plans.index') }}">
                                    <i class="bi bi-credit-card"></i>
                                    <span>{{ __('Plans') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-subscriptions') || vendorCan('cancel-subscriptions'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.subscriptions*') ? 'active' : '' }}" href="{{ route('vendor.subscriptions.index') }}">
                                    <i class="bi bi-credit-card"></i>
                                    <span>{{ __('Subscriptions') }}</span>
                                </a>
                            </li>
                        @endif

                        @if(vendorCan('view-customers') || vendorCan('view-vendor-users') || vendorCan('manage-vendor-users') || vendorCan('manage-settings') || vendorCan('edit-profile'))
                            <li class="nav-item mt-3">
                                <small class="text-muted px-3 text-uppercase fw-bold">{{ __('Customers & Account') }}</small>
                            </li>
                        @endif
                        @if(vendorCan('view-customers'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.customers*') ? 'active' : '' }}" href="{{ route('vendor.customers.index') }}">
                                    <i class="bi bi-people"></i>
                                    <span>{{ __('Customers') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-vendor-users') || vendorCan('manage-vendor-users'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.vendor-users*') ? 'active' : '' }}" href="{{ route('vendor.vendor-users.index') }}">
                                    <i class="bi bi-people"></i>
                                    <span>{{ __('Vendor Users') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('manage-settings'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.settings*') ? 'active' : '' }}" href="{{ route('vendor.settings.index') }}">
                                    <i class="bi bi-gear"></i>
                                    <span>{{ __('Settings') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('edit-profile'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('profile*') ? 'active' : '' }}" href="{{ route('profile') }}">
                                    <i class="bi bi-person"></i>
                                    <span>{{ __('Profile') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-category-requests') || vendorCan('view-variant-requests') || vendorCan('view-tickets') || vendorCan('manage-tickets'))
                            <li class="nav-item mt-3">
                                <small class="text-muted px-3 text-uppercase fw-bold">{{ __('Requests & Support') }}</small>
                            </li>
                        @endif
                        @if(vendorCan('view-category-requests'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.category-requests*') ? 'active' : '' }}" href="{{ route('vendor.category-requests.index') }}">
                                    <i class="bi bi-inbox"></i>
                                    <span>{{ __('Category Requests') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-variant-requests'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.variant-requests*') ? 'active' : '' }}" href="{{ route('vendor.variant-requests.index') }}">
                                    <i class="bi bi-inbox"></i>
                                    <span>{{ __('Variant Requests') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-tickets') || vendorCan('manage-tickets'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.tickets*') ? 'active' : '' }}" href="{{ route('vendor.tickets.index') }}">
                                    <i class="bi bi-inbox"></i>
                                    <span>{{ __('Tickets') }}</span>
                                </a>
                            </li>
                        @endif

                        @if(vendorCan('view-reports') || vendorCan('view-earnings-reports') || vendorCan('view-vendor-performance-reports') || vendorCan('view-product-performance-reports'))
                            <li class="nav-item mt-3">
                                <small class="text-muted px-3 text-uppercase fw-bold">{{ __('Analytics') }}</small>
                            </li>
                        @endif
                        @if(vendorCan('view-reports'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.reports.index') ? 'active' : '' }}" href="{{ route('vendor.reports.index') }}">
                                    <i class="bi bi-graph-up"></i>
                                    <span>{{ __('Reports & Analytics') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-earnings-reports'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.reports.earnings') ? 'active' : '' }}" href="{{ route('vendor.reports.earnings') }}">
                                    <i class="bi bi-wallet2"></i>
                                    <span>{{ __('Earnings Dashboard') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-vendor-performance-reports'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.reports.vendor-performance') ? 'active' : '' }}" href="{{ route('vendor.reports.vendor-performance') }}">
                                    <i class="bi bi-person-lines-fill"></i>
                                    <span>{{ __('Vendor Performance') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(vendorCan('view-product-performance-reports'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.reports.product-performance') ? 'active' : '' }}" href="{{ route('vendor.reports.product-performance') }}">
                                    <i class="bi bi-bar-chart-line"></i>
                                    <span>{{ __('Product Performance') }}</span>
                                </a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('help*') ? 'active' : '' }}" href="{{ route('help') }}">
                                <i class="bi bi-question-circle"></i>
                                <span>{{ __('Help & Support') }}</span>
                            </a>
                        </li>

                    @else
                        <!-- Default Menu (for users without specific role) -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2"></i>
                                <span>{{ __('Dashboard') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('profile*') ? 'active' : '' }}" href="{{ route('profile') }}">
                                <i class="bi bi-person"></i>
                                <span>{{ __('Profile') }}</span>
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </nav>
    </div>
</aside>
