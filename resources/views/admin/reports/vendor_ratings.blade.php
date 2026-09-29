@extends('layouts.app')

@php
    $page = 'vendor_ratings';
@endphp

@section('title', __('Vendor Ratings'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Vendor Ratings') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Vendor Ratings') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage vendor ratings') }}</p>
            </div>
        </div>

        @if(($ratings ?? collect())->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Ratings') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Vendor') }}</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Rating') }}</th>
                                    <th>{{ __('Comment') }}</th>
                                    <th>{{ __('Visible') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ratings as $rating)
                                    <tr>
                                        <td>{{ $rating->id }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $rating->vendor?->name }}</div>
                                            <small class="text-muted">#{{ $rating->vendor_id }}</small>
                                        </td>
                                        <td>
                                            {{ $rating->user?->name ?? '-' }}
                                            <div><small class="text-muted">#{{ $rating->user_id }}</small></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">{{ (int) $rating->rating }}/5</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ \Illuminate\Support\Str::limit($rating->comment, 80) }}</span>
                                        </td>
                                        <td>
                                            @if($rating->is_visible)
                                                <span class="badge bg-success">{{ __('Visible') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Hidden') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ optional($rating->created_at)->format('Y-m-d H:i') }}</small>
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('admin.vendor-ratings.toggle-visibility', $rating) }}"
                                                  method="POST"
                                                  class="d-inline vendor-toggle-visibility-form">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ ! $rating->is_visible ? 'btn-success' : 'btn-danger' }}">
                                                    @if($rating->is_visible)
                                                        <i class="bi bi-eye-slash me-1"></i>{{ __('Hide') }}
                                                    @else
                                                        <i class="bi bi-eye me-1"></i>{{ __('Show') }}
                                                    @endif
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $ratings->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-star-half fs-1 text-muted"></i>
                <p class="text-muted mt-3">{{ __('No vendor ratings found.') }}</p>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const forms = document.querySelectorAll('.vendor-toggle-visibility-form');

                forms.forEach(function (form) {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();

                        const isVisible = this.closest('tr').querySelector('.badge.bg-success') !== null;
                        const actionText = isVisible
                            ? '{{ __('This rating will be hidden from vendor details and averages.') }}'
                            : '{{ __('This rating will be visible in vendor details and averages.') }}';

                        Swal.fire({
                            title: '{{ __('Are you sure?') }}',
                            text: actionText,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: '{{ __('Yes, continue') }}',
                            cancelButtonText: '{{ __('Cancel') }}',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection


