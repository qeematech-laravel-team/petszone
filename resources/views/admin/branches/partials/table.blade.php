@php
    use Illuminate\Support\Str;
@endphp

@if($branches->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Vendor') }}</th>
                    <th>{{ __('Address') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $branch)
                    <tr>
                        <td>
                            <strong>{{ $branch->getTranslation('name', app()->getLocale()) }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $branch->getTranslation('name', 'en') }} / {{ $branch->getTranslation('name', 'ar') }}
                            </small>
                        </td>
                        <td>
                            @if($branch->vendor)
                                <span class="badge bg-info">{{ $branch->vendor->getTranslation('name', app()->getLocale()) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ Str::limit($branch->address, 50) }}</small>
                        </td>
                        <td>
                            @if($branch->phone)
                                <a href="tel:{{ $branch->phone }}" class="text-decoration-none">
                                    <i class="bi bi-telephone me-1"></i>{{ $branch->phone }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($branch->latitude && $branch->longitude)
                                <a href="https://www.google.com/maps?q={{ $branch->latitude }},{{ $branch->longitude }}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-geo-alt me-1"></i>{{ __('View Map') }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input toggle-active-btn" type="checkbox"
                                    id="toggleActive{{ $branch->id }}"
                                    data-branch-id="{{ $branch->id }}"
                                    data-toggle-url="{{ route('admin.branches.toggle-active', $branch) }}"
                                    {{ $branch->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="toggleActive{{ $branch->id }}">
                                    @if($branch->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                </label>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">{{ $branch->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.branches.show', $branch) }}"
                                    class="btn btn-outline-info" title="{{ __('View') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.branches.edit', $branch) }}"
                                    class="btn btn-outline-primary" title="{{ __('Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-outline-danger delete-branch-btn"
                                    title="{{ __('Delete') }}"
                                    data-branch-id="{{ $branch->id }}"
                                    data-branch-name="{{ $branch->getTranslation('name', app()->getLocale()) }}"
                                    data-delete-url="{{ route('admin.branches.destroy', $branch) }}">
                                    <i class="bi bi-trash"></i>
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
        <p class="text-muted mt-3">{{ __('No branches found.') }}</p>
        <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>{{ __('Create First Branch') }}
        </a>
    </div>
@endif
