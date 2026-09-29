@if($sliders->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 100px;">{{ __('Image') }}</th>
                    <th>{{ __('Created At') }}</th>
                    <th>{{ __('Updated At') }}</th>
                    <th class="text-end" style="width: 150px;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sliders as $slider)
                    <tr>
                        <td>
                            @if($slider->image)
                                <img src="{{ asset('storage/' . $slider->image) }}" 
                                     alt="{{ __('Slider Image') }}"
                                     class="img-thumbnail" 
                                     style="width: 80px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 60px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $slider->created_at->format('M d, Y H:i') }}</small>
                        </td>
                        <td>
                            <small class="text-muted">{{ $slider->updated_at->format('M d, Y H:i') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.sliders.show', $slider->id) }}" 
                                   class="btn btn-sm btn-outline-info" 
                                   data-bs-toggle="tooltip" 
                                   title="{{ __('View') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.sliders.edit', $slider->id) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   data-bs-toggle="tooltip" 
                                   title="{{ __('Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger delete-slider-btn" 
                                        data-id="{{ $slider->id }}"
                                        data-image="{{ $slider->image }}"
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
@else
    <div class="text-center py-5">
        <i class="bi bi-images fs-1 text-muted"></i>
        <p class="text-muted mt-3">{{ __('No sliders found.') }}</p>
        <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary mt-2">
            <i class="bi bi-plus-lg me-2"></i>{{ __('Add Your First Slider') }}
        </a>
    </div>
@endif
