@if($plans->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Price') }}</th>
                    <th>{{ __('Duration') }}</th>
                    <th>{{ __('Max Products') }}</th>
                    <th>{{ __('Can Feature') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Featured') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                    <tr>
                        <td>
                            <strong>{{ $plan->getTranslation('name', app()->getLocale()) }}</strong>
                        </td>
                        <td>
                            <span class="fw-bold text-primary">{{ $plan->getRawOriginal('price') }} {{ setting('currency', 'USD') }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $plan->duration_days }} {{ __('days') }}</span>
                        </td>
                        <td>
                            @if($plan->max_products_count)
                                <span class="badge bg-secondary">{{ $plan->max_products_count }}</span>
                            @else
                                <span class="text-muted">{{ __('Unlimited') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($plan->can_feature_products)
                                <span class="badge bg-success">{{ __('Yes') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('No') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($plan->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($plan->is_featured)
                                <span class="badge bg-primary">{{ __('Featured') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $plan->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="">
                                <a href="{{ route('admin.plans.show', $plan) }}"
                                    class="btn btn-sm text-center btn-outline-info" title="{{ __('View') }}">
                                    <i class="bi bi-eye m-0"></i>
                                </a>
                                <a href="{{ route('admin.plans.edit', $plan) }}"
                                    class="btn btn-sm text-center btn-outline-primary" title="{{ __('Edit') }}">
                                    <i class="bi bi-pencil m-0"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger delete-plan-btn"
                                    title="{{ __('Delete') }}"
                                    data-plan-id="{{ $plan->id }}"
                                    data-plan-name="{{ $plan->getTranslation('name', app()->getLocale()) }}"
                                    data-delete-url="{{ route('admin.plans.destroy', $plan) }}">
                                    <i class="bi bi-trash m-0"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-5">
        <i class="bi bi-credit-card display-1 text-muted"></i>
        <p class="text-muted mt-3">{{ __('No plans found.') }}</p>
        <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>{{ __('Create First Plan') }}
        </a>
    </div>
@endif
