@extends('layouts.app')

@php
    $page = 'settings';
@endphp

@section('title', 'Settings')

@section('content')

    <div class="container-fluid p-4 p-lg-4">

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Settings') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage your application preferences and configuration') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" form="settings-form" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>{{ __('Save Changes') }}
                </button>
            </div>
        </div>

        <!-- Settings Container -->
        <div class="settings-layout">
            <form id="settings-form" action="{{ route('admin.settings.update') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Settings Content -->
                    <div class="col-lg-12 settings-content">
                        {{-- App Name (string) --}}
                        <div class="form-group mb-3">
                            <label for="app_name">{{ __('App Name') }}</label>
                            <input type="text" class="form-control" id="app_name" name="app_name"
                                value="{{ setting('app_name', '') }}">
                        </div>

                        {{-- App Logo (image) --}}
                        <div class="form-group mb-4">
                            <label for="app_logo" class="form-label">{{ __('App Logo') }}</label>
                            <div class="card">
                                <div class="card-body">
                                    <!-- Upload Zone -->
                                    <div class="file-upload-zone" id="app_logo_zone"
                                         data-existing-image="{{ setting('app_logo') ? asset('storage/' . setting('app_logo')) : '' }}">
                                        <div class="upload-zone-content" id="app_logo_content">
                                            <i class="bi bi-cloud-upload display-4 text-muted mb-3"></i>
                                            <h5>{{ __('Drop files here or click to browse') }}</h5>
                                            <p class="text-muted">{{ __('Support for image files (Max 10MB each)') }}</p>
                                        </div>
                                        <!-- Image Preview (hidden by default) -->
                                        <div class="upload-zone-preview" id="app_logo_preview" style="display: none;">
                                            <img id="app_logo_preview_img" src="" alt="Preview"
                                                class="preview-image">
                                            <div class="preview-overlay">
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="removeImage('app_logo')">
                                                    <i class="bi bi-trash"></i> {{ __('Remove') }}
                                                </button>
                                            </div>
                                        </div>
                                        <input type="file" class="d-none" id="app_logo" name="app_logo" accept="image/*"
                                            onchange="handleFileSelect(this, 'app_logo')">
                                    </div>

                                    <!-- File Info (hidden by default) -->
                                    <div id="app_logo_info" class="mt-3" style="display: none;">
                                        <div class="d-flex align-items-center justify-content-between p-2  rounded">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-file-earmark-image me-2 text-primary"></i>
                                                <div>
                                                    <div class="fw-medium small" id="app_logo_name"></div>
                                                    <small class="text-muted" id="app_logo_size"></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- App Icon (image) --}}
                        <div class="form-group mb-4">
                            <label for="app_icon" class="form-label">{{ __('App Icon') }}</label>
                            <div class="card">
                                <div class="card-body">
                                    <!-- Upload Zone -->
                                    <div class="file-upload-zone" id="app_icon_zone"
                                         data-existing-image="{{ setting('app_icon') ? asset('storage/' . setting('app_icon')) : '' }}">
                                        <div class="upload-zone-content" id="app_icon_content">
                                            <i class="bi bi-cloud-upload display-4 text-muted mb-3"></i>
                                            <h5>{{ __('Drop files here or click to browse') }}</h5>
                                            <p class="text-muted">{{ __('Support for image files (Max 10MB each)') }}</p>
                                        </div>
                                        <!-- Image Preview (hidden by default) -->
                                        <div class="upload-zone-preview" id="app_icon_preview" style="display: none;">
                                            <img id="app_icon_preview_img" src="" alt="Preview"
                                                class="preview-image">
                                            <div class="preview-overlay">
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="removeImage('app_icon')">
                                                    <i class="bi bi-trash"></i> {{ __('Remove') }}
                                                </button>
                                            </div>
                                        </div>
                                        <input type="file" class="d-none" id="app_icon" name="app_icon"
                                            accept="image/*" onchange="handleFileSelect(this, 'app_icon')">
                                    </div>

                                    <!-- File Info (hidden by default) -->
                                    <div id="app_icon_info" class="mt-3" style="display: none;">
                                        <div class="d-flex align-items-center justify-content-between p-2  rounded">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-file-earmark-image me-2 text-primary"></i>
                                                <div>
                                                    <div class="fw-medium small" id="app_icon_name"></div>
                                                    <small class="text-muted" id="app_icon_size"></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Profit Type [subscription, percentage] (string) --}}
                        <div class="form-group mb-3">
                            <label for="profit_type">{{ __('Profit Type') }}</label>
                            <select class="form-control" id="profit_type" name="profit_type">
                                <option value="subscription"
                                    {{ setting('profit_type', '') == 'subscription' ? 'selected' : '' }}>
                                    {{ __('Subscription') }}</option>
                                <option value="commission"
                                    {{ setting('profit_type', '') == 'commission' ? 'selected' : '' }}>
                                    {{ __('Commission') }}</option>
                            </select>
                        </div>

                        {{-- Profit Value (number) --}}
                        <div class="form-group mb-3">
                            <label for="profit_value">{{ __('Profit Value') }}</label>
                            <input type="number" class="form-control" id="profit_value" name="profit_value"
                                value="{{ setting('profit_value', '') }}">
                        </div>

                        {{-- Referral Points (number) --}}
                        <div class="form-group mb-3">
                            <label for="referral_points">{{ __('Referral Points') }}</label>
                            <input type="number" class="form-control" id="referral_points" name="referral_points"
                                value="{{ setting('referral_points', '0') }}">
                        </div>

                        {{-- Cashback Points Rate (number) --}}
                        <div class="form-group mb-3">
                            <label for="cache_back_points_rate">{{ __('Cashback Points Rate') }}</label>
                            <input type="number" class="form-control" id="cache_back_points_rate" name="cache_back_points_rate"
                                value="{{ setting('cache_back_points_rate', '0') }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

@endsection

@push('modals')
@endpush

@push('scripts')
    <style>
        .file-upload-zone {
            min-height: 200px;
            transition: all 0.3s ease;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            position: relative;
        }

        .file-upload-zone:hover {
            border-color: #2c7eb8;
            background-color: #f8f9fa;
        }

        .file-upload-zone.has-image {
            padding: 0;
            border: 2px dashed #dee2e6;
        }

        .upload-zone-preview {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .upload-zone-preview .preview-image {
            width: 250px;
            height: 250px;
            min-height: 200px;
            object-fit: contain;
            background: #f8f9fa;
        }

        .upload-zone-preview .preview-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .upload-zone-preview:hover .preview-overlay {
            opacity: 1;
        }

        .upload-zone-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
        }
    </style>

    <script>
        (function() {
            'use strict';

            // Format file size
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }

            // Handle file selection
            window.handleFileSelect = function(input, fieldId) {
                const file = input.files[0];
                if (!file) return;

                // Check file size (10MB max)
                if (file.size > 10 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 10MB.');
                    input.value = '';
                    return;
                }

                // Check if it's an image
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file.');
                    input.value = '';
                    return;
                }

                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Show preview
                    const previewDiv = document.getElementById(fieldId + '_preview');
                    const previewImg = document.getElementById(fieldId + '_preview_img');
                    const contentDiv = document.getElementById(fieldId + '_content');
                    const infoDiv = document.getElementById(fieldId + '_info');
                    const nameSpan = document.getElementById(fieldId + '_name');
                    const sizeSpan = document.getElementById(fieldId + '_size');
                    const zone = document.getElementById(fieldId + '_zone');

                    if (previewDiv && previewImg && contentDiv) {
                        previewImg.src = e.target.result;
                        previewDiv.style.display = 'flex';
                        contentDiv.style.display = 'none';
                        zone.classList.add('has-image');
                    }

                    // Show file info
                    if (infoDiv && nameSpan && sizeSpan) {
                        nameSpan.textContent = file.name;
                        sizeSpan.textContent = formatFileSize(file.size);
                        infoDiv.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            };

            // Remove image
            window.removeImage = function(fieldId) {
                const input = document.getElementById(fieldId);
                const previewDiv = document.getElementById(fieldId + '_preview');
                const contentDiv = document.getElementById(fieldId + '_content');
                const infoDiv = document.getElementById(fieldId + '_info');
                const zone = document.getElementById(fieldId + '_zone');

                if (input) {
                    input.value = '';
                }
                if (previewDiv) {
                    previewDiv.style.display = 'none';
                }
                if (contentDiv) {
                    contentDiv.style.display = 'flex';
                }
                if (infoDiv) {
                    infoDiv.style.display = 'none';
                }
                if (zone) {
                    zone.classList.remove('has-image');
                }
            };

            // Initialize drag and drop for both upload zones
            function initDragAndDrop(fieldId) {
                const zone = document.getElementById(fieldId + '_zone');
                const input = document.getElementById(fieldId);

                if (!zone || !input) return;

                // Click to browse
                zone.addEventListener('click', function(e) {
                    if (e.target.tagName !== 'BUTTON' && e.target.closest('.preview-overlay') === null) {
                        input.click();
                    }
                });

                // Drag and drop
                zone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    zone.classList.add('dragover');
                });

                zone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    zone.classList.remove('dragover');
                });

                zone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    zone.classList.remove('dragover');

                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        // Create a fake event object
                        const fakeEvent = {
                            target: {
                                files: files
                            }
                        };
                        // Temporarily set input files
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(files[0]);
                        input.files = dataTransfer.files;
                        handleFileSelect(input, fieldId);
                    }
                });
            }

            // Load existing images on page load
            function loadExistingImage(fieldId) {
                const zone = document.getElementById(fieldId + '_zone');
                if (!zone) return;

                const existingImageUrl = zone.getAttribute('data-existing-image');
                if (existingImageUrl && existingImageUrl.trim() !== '') {
                    const previewDiv = document.getElementById(fieldId + '_preview');
                    const previewImg = document.getElementById(fieldId + '_preview_img');
                    const contentDiv = document.getElementById(fieldId + '_content');

                    if (previewDiv && previewImg && contentDiv) {
                        // Set image source
                        previewImg.src = existingImageUrl;
                        // Show preview and hide content
                        previewDiv.style.display = 'flex';
                        contentDiv.style.display = 'none';
                        zone.classList.add('has-image');

                        // Extract filename from URL for display
                        const fileName = existingImageUrl.split('/').pop();
                        const infoDiv = document.getElementById(fieldId + '_info');
                        const nameSpan = document.getElementById(fieldId + '_name');
                        const sizeSpan = document.getElementById(fieldId + '_size');

                        if (infoDiv && nameSpan) {
                            nameSpan.textContent = fileName || 'Existing image';
                            // Size is not available from stored path
                            if (sizeSpan) {
                                sizeSpan.textContent = 'Existing image';
                            }
                            infoDiv.style.display = 'block';
                        }
                    }
                }
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                initDragAndDrop('app_logo');
                initDragAndDrop('app_icon');

                // Load existing images
                loadExistingImage('app_logo');
                loadExistingImage('app_icon');
            });
        })();
    </script>
@endpush
