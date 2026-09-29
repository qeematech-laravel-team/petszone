@extends('layouts.app')

@php
    $page = 'forms';
@endphp

@section('title', 'Forms')

@section('content')

    <div class="container-fluid p-4">

        <!-- Contact Form -->
        <div class="row g-4 mb-5">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person me-2 text-primary"></i>
                            {{ __('Update Your Profile') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form  action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" @submit.prevent="submitForm()">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group floating-label">
                                        <input type="text" class="form-control" name="name" placeholder=" "
                                            x-model="form.name" @input="validateField('name')"
                                            :class="getFieldClass('name')" value="{{ $user->name }}" required>
                                        <label class="form-label">{{ __('Name') }}</label>
                                        <div class="invalid-feedback" x-show="errors.name" x-text="errors.name">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group floating-label">
                                        <input type="email" class="form-control" name="email" placeholder=" "
                                            x-model="form.email" @input="validateField('email')"
                                            :class="getFieldClass('email')" value="{{ $user->email }}" required>
                                        <label class="form-label">{{ __('Email') }}</label>
                                        <div class="invalid-feedback" x-show="errors.email" x-text="errors.email">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group floating-label">
                                        <input type="text" class="form-control" name="phone" placeholder=" "
                                            x-model="form.phone" @input="validateField('phone')"
                                            :class="getFieldClass('phone')" value="{{ $user->phone }}" required>
                                        <label class="form-label">{{ __('Phone') }}</label>
                                        <div class="invalid-feedback" x-show="errors.phone" x-text="errors.phone"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card shadow-sm">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-image me-2 text-primary"></i>
                                                {{ __('Profile Picture') }}
                                            </h5>
                                        </div>

                                        <div class="card-body">

                                            {{-- Current Profile Image --}}
                                            <div class="text-center mb-4" id="imagePreview">
                                                <img
                                                    id="preview"
                                                    src="{{ $user->image }}"
                                                    class="rounded-circle shadow"
                                                    style="width:120px; height:120px; object-fit:cover;"
                                                    alt="Profile Image"
                                                >
                                                <p class="text-muted mt-2">{{ __('Profile Picture Preview') }}</p>
                                            </div>



                                            {{-- Upload Area --}}
                                            <div x-data="fileUploadForm()">

                                                <div
                                                    class="file-upload-zone text-center p-4 border rounded-3 cursor-pointer"
                                                    :class="{ 'dragover': dragOver }"
                                                    @drop.prevent="handleDrop($event)"
                                                    @dragover.prevent="dragOver = true"
                                                    @dragleave="dragOver = false"
                                                    @click="$refs.fileInput.click()"
                                                >
                                                    <i class="bi bi-cloud-upload display-4 text-primary mb-3"></i>
                                                    <h6 class="fw-bold">{{ __('Upload New Picture') }}</h6>
                                                    <p class="text-muted mb-0">
                                                        {{ __('Drag & drop or click to select an image (Max 10MB)') }}
                                                    </p>

                                                    <input
                                                    type="file"
                                                    name="image"
                                                    class="d-none"
                                                    x-ref="fileInput"
                                                    accept="image/*"
                                                    onchange="previewImage(this)"
                                                >

                                                </div>

                                                {{-- Selected File Preview --}}
                                                <div x-show="files.length > 0" class="mt-4">
                                                    <h6>{{ __('Selected File') }}</h6>

                                                    <template x-for="file in files" :key="file.id">
                                                        <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                                                            <div class="d-flex align-items-center">
                                                                <i class="bi bi-image me-3 text-primary fs-4"></i>
                                                                <div>
                                                                    <div class="fw-medium" x-text="file.name"></div>
                                                                    <small class="text-muted" x-text="file.size"></small>
                                                                </div>
                                                            </div>

                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                @click="removeFile(file.id)"
                                                            >
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                        <span x-show="!isSubmitting">
                                            <i class="bi bi-send me-2"></i>{{ __('Update Profile') }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('modals')
@endpush

@push('scripts')
<script>
    function previewImage(input) {
        const preview = document.getElementById('preview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>

@endpush
