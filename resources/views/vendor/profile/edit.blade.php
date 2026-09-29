@extends('layouts.app')

@section('title', 'Vendor Profile')

@section('content')
    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Vendor Profile</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="col-12">
                                <form action="{{ route('vendor.profile.update') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="name">{{ __('Name English') }}</label>
                                                <input type="text" class="form-control" name="name[en]"
                                                    value="{{ $vendor->getTranslation('name', 'en') }}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="name">{{ __('Name Arabic') }}</label>
                                                <input type="text" class="form-control" name="name[ar]"
                                                    value="{{ $vendor->getTranslation('name', 'ar') }}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" class="form-control" name="email"
                                                    value="{{ $user->email }}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="phone">Phone</label>
                                                <input type="text" class="form-control" name="phone"
                                                    value="{{ $user->phone }}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="address">Address</label>
                                                <input type="text" class="form-control" name="address"
                                                    value="{{ $vendor->address }}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label for="image" class="form-label">{{ __('Vendor Image') }}</label>

                                            @if ($vendor->image)
                                                <div class="mb-2">
                                                    <img src="{{ $vendor->image }}" alt="Current image"
                                                        class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                                    <p class="text-muted small mt-1">{{ __('Current image') }}</p>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="image_removed"
                                                            id="image_removed" value="1">
                                                        <label class="form-check-label" for="image_removed">
                                                            {{ __('Remove current image') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif

                                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                                id="image" name="image" accept="image/*"
                                                onchange="previewImage(this)">
                                            @error('image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small
                                                class="text-muted">{{ __('Leave empty to keep current image. Recommended size: 300x300px. Max size: 3MB') }}</small>

                                            <!-- Image Preview -->
                                            <div id="imagePreview" class="mt-3" style="display: none;">
                                                <img id="preview" src="" alt="Preview" class="img-thumbnail"
                                                    style="max-width: 200px; max-height: 200px;">
                                                <p class="text-muted small mt-1">{{ __('New image preview') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewDiv = document.getElementById('imagePreview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewDiv.style.display = 'block';
                };

                reader.readAsDataURL(input.files[0]);
            } else {
                previewDiv.style.display = 'none';
            }
        }
    </script>
@endpush
