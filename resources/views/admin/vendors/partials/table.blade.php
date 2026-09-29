@if($vendors->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Image') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Owner') }}</th>
                    <th>{{ __('Phone') }}</th>
                    @if (setting('profit_type') == 'subscription')
                    <th>{{ __('Plan') }}</th>
                    @else
                    <th>{{ __('Commission Rate') }}</th>
                    @endif
                    <th>{{ __('Balance') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Featured') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendors as $vendor)
                    <tr>
                        <td>
                            <img src="{{ $vendor->image }}" alt="{{ $vendor->getTranslation('name', app()->getLocale()) }}"
                                class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td>
                            <strong>{{ $vendor->getTranslation('name', app()->getLocale()) }}</strong>
                        </td>
                        <td>
                            @if($vendor->owner)
                                <div>
                                    <div class="fw-medium">{{ $vendor->owner->name }}</div>
                                    <small class="text-muted">{{ $vendor->owner->email }}</small>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            {{ $vendor->phone ?? '-' }}
                        </td>
                        <td>
                            @if (setting('profit_type') == 'subscription')
                                @if($vendor->plan)
                                <span class="badge bg-info">{{ $vendor->plan->getTranslation('name', app()->getLocale()) }}</span>
                                @else
                                    <span class="text-muted">{{ __('No Plan') }}</span>
                                @endif
                            @else
                                <span class="fw-bold">{{ $vendor->commission_rate }}%</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold text-success">{{ number_format($vendor->balance, 2) }} {{ setting('currency', 'USD') }}</span>
                        </td>
                        <td>
                            @if($vendor->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($vendor->is_featured)
                                <span class="badge bg-primary">{{ __('Featured') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $vendor->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="">
                                <a href="{{ route('admin.vendors.show', $vendor) }}"
                                    class="btn btn-sm text-center btn-outline-info" title="{{ __('View') }}">
                                    <i class="bi bi-eye m-0"></i>
                                </a>
                                <a href="{{ route('admin.vendors.edit', $vendor) }}"
                                    class="btn btn-sm text-center btn-outline-primary" title="{{ __('Edit') }}">
                                    <i class="bi bi-pencil m-0"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger delete-vendor-btn"
                                    title="{{ __('Delete') }}"
                                    data-vendor-id="{{ $vendor->id }}"
                                    data-vendor-name="{{ $vendor->getTranslation('name', app()->getLocale()) }}"
                                    data-delete-url="{{ route('admin.vendors.destroy', $vendor) }}">
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
        <i class="bi bi-shop display-1 text-muted"></i>
        <p class="text-muted mt-3">{{ __('No vendors found.') }}</p>
        <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>{{ __('Create First Vendor') }}
        </a>
    </div>
@endif
