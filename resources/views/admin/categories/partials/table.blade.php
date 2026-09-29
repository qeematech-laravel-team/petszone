@if($categories->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Image') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Parent') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Featured') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>
                            <img src="{{ $category->image }}" alt="{{ $category->getTranslation('name', app()->getLocale()) }}"
                                class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td>
                            <strong>{{ $category->getTranslation('name', app()->getLocale()) }}</strong>
                            @if($category->children->count() > 0)
                                <span class="badge bg-info ms-2">{{ $category->children->count() }} {{ __('subcategories') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($category->parent)
                                {{ is_array($category->parent->name) ? $category->parent->name[app()->getLocale()] ?? reset($category->parent->name) : $category->parent->name }}
                            @else
                                <span class="text-muted">{{ __('Root') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($category->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($category->is_featured)
                                <span class="badge bg-primary">{{ __('Featured') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $category->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="">
                                <a href="{{ route('admin.categories.show', $category) }}"
                                    class="btn btn-sm text-center btn-outline-info" title="{{ __('View') }}">
                                    <i class="bi bi-eye m-0"></i>
                                </a>
                                <a href="{{ route('admin.categories.edit', $category) }}"
                                    class="btn btn-sm text-center btn-outline-primary" title="{{ __('Edit') }}">
                                    <i class="bi bi-pencil m-0"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger delete-category-btn"
                                    title="{{ __('Delete') }}"
                                    data-category-id="{{ $category->id }}"
                                    data-category-name="{{ $category->getTranslation('name', app()->getLocale()) }}"
                                    data-delete-url="{{ route('admin.categories.destroy', $category) }}"
                                    data-has-children="{{ $category->children->count() > 0 ? 'true' : 'false' }}"
                                    data-children-count="{{ $category->children->count() }}">
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
        <i class="bi bi-folder-x display-1 text-muted"></i>
        <p class="text-muted mt-3">{{ __('No categories found.') }}</p>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>{{ __('Create First Category') }}
        </a>
    </div>
@endif
