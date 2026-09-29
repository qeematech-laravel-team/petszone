@if($variants->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Options') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Required') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($variants as $variant)
                    <tr>
                        <td>
                            <strong>{{ $variant->getTranslation('name', app()->getLocale()) }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $variant->getTranslation('name', 'en') }} / {{ $variant->getTranslation('name', 'ar') }}
                            </small>
                        </td>
                        <td>
                            @if($variant->options->count() > 0)
                                <span class="badge bg-info">{{ $variant->options->count() }} {{ __('options') }}</span>
                                <br>
                                <small class="text-muted">
                                    @foreach($variant->options->take(3) as $option)
                                        {{ $option->getTranslation('name', app()->getLocale()) }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                    @if($variant->options->count() > 3)
                                        +{{ $variant->options->count() - 3 }} {{ __('more') }}
                                    @endif
                                </small>
                            @else
                                <span class="text-muted">{{ __('No options') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input toggle-active" 
                                       type="checkbox" 
                                       data-variant-id="{{ $variant->id }}"
                                       data-toggle-url="{{ route('admin.variants.toggle-active', $variant) }}"
                                       {{ $variant->is_active ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    @if($variant->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input toggle-required" 
                                       type="checkbox" 
                                       data-variant-id="{{ $variant->id }}"
                                       data-toggle-url="{{ route('admin.variants.toggle-required', $variant) }}"
                                       {{ $variant->is_required ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    @if($variant->is_required)
                                        <span class="badge bg-warning">{{ __('Required') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Optional') }}</span>
                                    @endif
                                </label>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">{{ $variant->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.variants.show', $variant) }}"
                                    class="btn btn-sm btn-outline-info" title="{{ __('View') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.variants.edit', $variant) }}"
                                    class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger delete-variant-btn"
                                    title="{{ __('Delete') }}"
                                    data-variant-id="{{ $variant->id }}"
                                    data-variant-name="{{ $variant->getTranslation('name', app()->getLocale()) }}"
                                    data-delete-url="{{ route('admin.variants.destroy', $variant) }}">
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
        <i class="bi bi-tags display-1 text-muted"></i>
        <p class="text-muted mt-3">{{ __('No variants found.') }}</p>
        <a href="{{ route('admin.variants.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>{{ __('Create First Variant') }}
        </a>
    </div>
@endif
