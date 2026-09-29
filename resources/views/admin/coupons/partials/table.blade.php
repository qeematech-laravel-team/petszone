@if($coupons->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Discount Value') }}</th>
                    <th>{{ __('Min Cart Amount') }}</th>
                    <th>{{ __('Usage Limit') }}</th>
                    <th>{{ __('Validity') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Orders') }}</th>
                    <th class="text-end" style="width: 150px;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($coupons as $coupon)
                    <tr>
                        <td>
                            <code class="fs-6">{{ $coupon->code }}</code>
                        </td>
                        <td>
                            @if($coupon->type === 'percentage')
                                <span class="badge bg-info">{{ __('Percentage') }}</span>
                            @else
                                <span class="badge bg-primary">{{ __('Fixed') }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>
                                @if($coupon->type === 'percentage')
                                    {{ number_format($coupon->discount_value, 0) }}%
                                @else
                                    {{ number_format($coupon->discount_value, 2) }} {{ setting('currency', 'EGP') }}
                                @endif
                            </strong>
                        </td>
                        <td>
                            @if($coupon->min_cart_amount > 0)
                                {{ number_format($coupon->min_cart_amount, 2) }} {{ setting('currency', 'EGP') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($coupon->usage_limit_per_user)
                                {{ $coupon->usage_limit_per_user }} {{ __('per user') }}
                            @else
                                <span class="text-muted">{{ __('Unlimited') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($coupon->start_date || $coupon->end_date)
                                <small class="text-muted">
                                    @if($coupon->start_date)
                                        {{ __('From') }}: {{ $coupon->start_date->format('M d, Y') }}<br>
                                    @endif
                                    @if($coupon->end_date)
                                        {{ __('To') }}: {{ $coupon->end_date->format('M d, Y') }}
                                    @endif
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($coupon->is_active)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('Active') }}
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('Inactive') }}
                                </span>
                            @endif
                            @if($coupon->isValid())
                                <br><small class="text-success">{{ __('Valid') }}</small>
                            @else
                                <br><small class="text-danger">{{ __('Invalid') }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $coupon->orders->count() }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.coupons.show', $coupon->id) }}" 
                                   class="btn btn-sm btn-outline-info" 
                                   data-bs-toggle="tooltip" 
                                   title="{{ __('View') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.coupons.edit', $coupon->id) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   data-bs-toggle="tooltip" 
                                   title="{{ __('Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger delete-coupon-btn" 
                                        data-id="{{ $coupon->id }}"
                                        data-code="{{ $coupon->code }}"
                                        data-bs-toggle="tooltip" 
                                        title="{{ __('Delete') }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $coupons->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="bi bi-ticket-perforated fs-1 text-muted"></i>
        <p class="text-muted mt-3">{{ __('No coupons found.') }}</p>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary mt-2">
            <i class="bi bi-plus-lg me-2"></i>{{ __('Add Your First Coupon') }}
        </a>
    </div>
@endif
