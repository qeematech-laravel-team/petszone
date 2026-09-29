@extends('layouts.app')

@php
    $page = 'settings';
@endphp

@section('title', __('Settings'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Settings') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage your vendor settings and preferences') }}</p>
            </div>
        </div>

        <!-- Settings Form -->
        <form action="{{ route('vendor.settings.update') }}" method="POST" id="settingsForm">
            @csrf
            @method('PUT')

            <!-- Settings Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="branch-tab" data-bs-toggle="tab" data-bs-target="#branch" type="button" role="tab" aria-controls="branch" aria-selected="true">
                                <i class="bi bi-shop me-2"></i>{{ __('Branch Settings') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" aria-selected="false">
                                <i class="bi bi-truck me-2"></i>{{ __('Shipping Settings') }}
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="settingsTabsContent">
                        <!-- Branch Settings Tab -->
                        <div class="tab-pane fade show active" id="branch" role="tabpanel" aria-labelledby="branch-tab">
                            <h5 class="mb-4">{{ __('Branch Management') }}</h5>

                            <!-- Allow Branch Users to Edit Stock -->
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="allow_branch_user_to_edit_stock" value="0">
                                    <input class="form-check-input" type="checkbox" id="allow_branch_user_to_edit_stock" name="allow_branch_user_to_edit_stock" value="1" {{ (isset($settings['allow_branch_user_to_edit_stock']) && $settings['allow_branch_user_to_edit_stock']->value) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_branch_user_to_edit_stock">
                                        <strong>{{ $defaultSettings['branch']['allow_branch_user_to_edit_stock']['label'] }}</strong>
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">{{ $defaultSettings['branch']['allow_branch_user_to_edit_stock']['description'] }}</small>
                            </div>

                            <!-- Allow Branch Users to Edit Price -->
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="allow_branch_user_to_edit_price" value="0">
                                    <input class="form-check-input" type="checkbox" id="allow_branch_user_to_edit_price" name="allow_branch_user_to_edit_price" value="1" {{ (isset($settings['allow_branch_user_to_edit_price']) && $settings['allow_branch_user_to_edit_price']->value) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_branch_user_to_edit_price">
                                        <strong>{{ $defaultSettings['branch']['allow_branch_user_to_edit_price']['label'] }}</strong>
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">{{ $defaultSettings['branch']['allow_branch_user_to_edit_price']['description'] }}</small>
                            </div>
                        </div>

                        <!-- Shipping Settings Tab -->
                        <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                            <h5 class="mb-4">{{ __('Shipping Configuration') }}</h5>

                            <!-- Allow Free Shipping Threshold -->
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="allow_free_shipping_threshold" value="0">
                                    <input class="form-check-input" type="checkbox" id="allow_free_shipping_threshold" name="allow_free_shipping_threshold" value="1" {{ (isset($settings['allow_free_shipping_threshold']) && $settings['allow_free_shipping_threshold']->value) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_free_shipping_threshold">
                                        <strong>{{ $defaultSettings['shipping']['allow_free_shipping_threshold']['label'] }}</strong>
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">{{ $defaultSettings['shipping']['allow_free_shipping_threshold']['description'] }}</small>
                            </div>

                            <!-- Minimum Order Amount for Free Shipping -->
                            <div class="mb-4">
                                <label for="free_shipping_threshold" class="form-label">
                                    <strong>{{ $defaultSettings['shipping']['free_shipping_threshold']['label'] }}</strong>
                                </label>
                                <input type="number" class="form-control @error('free_shipping_threshold') is-invalid @enderror" id="free_shipping_threshold" name="free_shipping_threshold" value="{{ old('free_shipping_threshold', isset($settings['free_shipping_threshold']) ? $settings['free_shipping_threshold']->value : 0) }}" min="0" step="0.01">
                                <small class="text-muted d-block mt-2">{{ $defaultSettings['shipping']['free_shipping_threshold']['description'] }}</small>
                                @error('free_shipping_threshold')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Shipping Cost Per KM -->
                            <div class="mb-4">
                                <label for="shipping_cost_per_km" class="form-label">
                                    <strong>{{ $defaultSettings['shipping']['shipping_cost_per_km']['label'] }}</strong>
                                </label>
                                <input type="number" class="form-control @error('shipping_cost_per_km') is-invalid @enderror" id="shipping_cost_per_km" name="shipping_cost_per_km" value="{{ old('shipping_cost_per_km', isset($settings['shipping_cost_per_km']) ? $settings['shipping_cost_per_km']->value : 0) }}" min="0" step="0.01">
                                <small class="text-muted d-block mt-2">{{ $defaultSettings['shipping']['shipping_cost_per_km']['description'] }}</small>
                                @error('shipping_cost_per_km')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Minimum Shipping Cost -->
                            <div class="mb-4">
                                <label for="minimum_shipping_cost" class="form-label">
                                    <strong>{{ $defaultSettings['shipping']['minimum_shipping_cost']['label'] }}</strong>
                                </label>
                                <input type="number" class="form-control @error('minimum_shipping_cost') is-invalid @enderror" id="minimum_shipping_cost" name="minimum_shipping_cost" value="{{ old('minimum_shipping_cost', isset($settings['minimum_shipping_cost']) ? $settings['minimum_shipping_cost']->value : 0) }}" min="0" step="0.01">
                                <small class="text-muted d-block mt-2">{{ $defaultSettings['shipping']['minimum_shipping_cost']['description'] }}</small>
                                @error('minimum_shipping_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Maximum Shipping Cost -->
                            <div class="mb-4">
                                <label for="maximum_shipping_cost" class="form-label">
                                    <strong>{{ $defaultSettings['shipping']['maximum_shipping_cost']['label'] }}</strong>
                                </label>
                                <input type="number" class="form-control @error('maximum_shipping_cost') is-invalid @enderror" id="maximum_shipping_cost" name="maximum_shipping_cost" value="{{ old('maximum_shipping_cost', isset($settings['maximum_shipping_cost']) ? $settings['maximum_shipping_cost']->value : 0) }}" min="0" step="0.01">
                                <small class="text-muted d-block mt-2">{{ $defaultSettings['shipping']['maximum_shipping_cost']['description'] }}</small>
                                @error('maximum_shipping_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>{{ __('Save Settings') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
