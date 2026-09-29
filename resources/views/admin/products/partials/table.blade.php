@if($products->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Image') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Vendor') }}</th>
                    <th>{{ __('SKU') }}</th>
                    <th>{{ __('Price') }}</th>
                    <th>{{ __('Stock') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Featured') }}</th>
                    <th>{{ __('Approved') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>
                            <img src="{{ $product->thumbnail }}"
                                 alt="{{ $product->getTranslation('name', app()->getLocale()) }}"
                                 class="img-thumbnail"
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td>
                            <strong>{{ $product->getTranslation('name', app()->getLocale()) }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $product->getTranslation('name', 'en') }} / {{ $product->getTranslation('name', 'ar') }}
                            </small>
                        </td>
                        <td>
                            @if($product->vendor)
                                <span class="badge bg-info">{{ $product->vendor->getTranslation('name', app()->getLocale()) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <code>{{ $product->sku }}</code>
                        </td>
                        <td>
                            <strong>{{ number_format( $product->manager()->price(), 2) }} {{ setting('currency') }}</strong>
                            @if($product->hasDiscount())
                                <br>
                                <small class="text-success">
                                    {{ number_format($product->final_price, 2) }} {{ setting('currency') }}
                                </small>
                            @endif
                        </td>
                        <td>
                            @if($product->isInStock())
                                @php
                                    $totalStock = 0;
                                    if ($product->type === 'simple') {
                                        $totalStock = $product->branchProductStocks->sum('quantity');
                                    } else {
                                        $totalStock = $product->variants->sum(function($variant) {
                                            return $variant->branchVariantStocks->sum('quantity');
                                        });
                                    }
                                @endphp
                                <span class="badge bg-success">{{ $totalStock }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Out of Stock') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($product->type === 'variable')
                                <span class="badge bg-primary">{{ __('Variable') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Simple') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input toggle-active-btn" type="checkbox"
                                    id="toggleActive{{ $product->id }}"
                                    data-product-id="{{ $product->id }}"
                                    data-toggle-url="{{ route('admin.products.toggle-active', $product) }}"
                                    {{ $product->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="toggleActive{{ $product->id }}">
                                    @if($product->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input toggle-featured-btn" type="checkbox"
                                    id="toggleFeatured{{ $product->id }}"
                                    data-product-id="{{ $product->id }}"
                                    data-toggle-url="{{ route('admin.products.toggle-featured', $product) }}"
                                    {{ $product->is_featured ? 'checked' : '' }}>
                                <label class="form-check-label" for="toggleFeatured{{ $product->id }}">
                                    @if($product->is_featured)
                                        <span class="badge bg-warning">{{ __('Featured') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('No') }}</span>
                                    @endif
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input toggle-approved-btn" type="checkbox"
                                    id="toggleApproved{{ $product->id }}"
                                    data-product-id="{{ $product->id }}"
                                    data-toggle-url="{{ route('admin.products.toggle-approved', $product) }}"
                                    {{ $product->is_approved ? 'checked' : '' }}>
                                <label class="form-check-label" for="toggleApproved{{ $product->id }}">
                                    @if($product->is_approved)
                                        <span class="badge bg-success">{{ __('Approved') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Pending') }}</span>
                                    @endif
                                </label>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">{{ $product->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.products.show', $product) }}"
                                    class="btn btn-outline-info" title="{{ __('View') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product) }}"
                                    class="btn btn-outline-primary" title="{{ __('Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-outline-danger delete-product-btn"
                                    title="{{ __('Delete') }}"
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->getTranslation('name', app()->getLocale()) }}"
                                    data-delete-url="{{ route('admin.products.destroy', $product) }}">
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
        <i class="bi bi-box-seam display-1 text-muted"></i>
        <p class="text-muted mt-3">{{ __('No products found.') }}</p>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>{{ __('Create First Product') }}
        </a>
    </div>
@endif
