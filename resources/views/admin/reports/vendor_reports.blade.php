@extends('layouts.app')

@php
    $page = 'vendor_reports';
@endphp

@section('title', __('Vendor Reports'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Vendor Reports') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Vendor Reports') }}</h1>
                <p class="text-muted mb-0">{{ __('Manage reported vendors') }}</p>
            </div>
        </div>

        @if(($reports ?? collect())->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Reports') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Vendor') }}</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Reason') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Handled By') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports as $report)
                                    <tr>
                                        <td>{{ $report->id }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $report->vendor?->name }}</div>
                                            <small class="text-muted">#{{ $report->vendor_id }}</small>
                                        </td>
                                        <td>
                                            {{ $report->user?->name ?? __('Guest') }}
                                            <div><small class="text-muted">#{{ $report->user_id ?? '-' }}</small></div>
                                        </td>
                                        <td>{{ $report->reason ?? '-' }}</td>
                                        <td class="text-muted">{{ \Illuminate\Support\Str::limit($report->description, 80) }}</td>
                                        <td>
                                            @php
                                                $status = $report->status ?? 'pending';
                                                $badge = $status === 'reviewed' ? 'bg-success' : ($status === 'ignored' ? 'bg-secondary' : 'bg-warning text-dark');
                                            @endphp
                                            <span class="badge {{ $badge }}">{{ ucfirst($status) }}</span>
                                        </td>
                                        <td>
                                            {{ $report->handler?->name ?? '-' }}
                                            @if($report->handled_at)
                                                <div><small class="text-muted">{{ $report->handled_at->format('Y-m-d H:i') }}</small></div>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ optional($report->created_at)->format('Y-m-d H:i') }}</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                @if($report->status !== 'reviewed')
                                                    <form action="{{ route('admin.vendor-reports.update-status', [$report, 'reviewed']) }}"
                                                          method="POST"
                                                          class="d-inline vendor-report-status-form">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="bi bi-check2-circle me-1"></i>{{ __('Mark as reviewed') }}
                                                        </button>
                                                    </form>
                                                @endif

            @if($report->status !== 'ignored')
                <form action="{{ route('admin.vendor-reports.update-status', [$report, 'ignored']) }}"
                      method="POST"
                      class="d-inline vendor-report-status-form ms-1">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-slash-circle me-1"></i>{{ __('Ignore') }}
                    </button>
                </form>
            @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-flag-fill fs-1 text-muted"></i>
                <p class="text-muted mt-3">{{ __('No vendor reports found.') }}</p>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const forms = document.querySelectorAll('.vendor-report-status-form');

                forms.forEach(function (form) {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();

                        const toStatus = this.action.split('/').pop();
                        const statusLabel = toStatus.charAt(0).toUpperCase() + toStatus.slice(1);

                        Swal.fire({
                            title: '{{ __('Are you sure?') }}',
                            text: '{{ __('You are about to change this report status to') }} ' + statusLabel + '.',
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

