@extends('layouts.app')

@php
    $page = 'reports';
@endphp

@section('title', __('Earnings Dashboard'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">{{ __('Reports & Analytics') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Earnings') }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">{{ __('Earnings Dashboard') }}</h1>
            <p class="text-muted mb-0">{{ __('Platform earnings overview') }}</p>
        </div>
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
            <i class="bi bi-sliders me-1"></i>{{ __('Filters') }}
        </button>
    </div>

    @php
        $paidTotal = $report['kpis']['paid_orders_total'] ?? 0;
        $totalCommission = $report['kpis']['total_commission'] ?? 0;
        $refundedTotal = $report['kpis']['refunded_total'] ?? 0;
        // Platform earnings ~ commission; net revenue for reference
        $platformEarnings = $totalCommission;
        $netRevenue = max(0, $paidTotal - $refundedTotal);
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Paid Revenue') }}</div>
                            <div class="fs-4 fw-semibold">
                                {{ number_format($paidTotal, 2) }} {{ setting('currency', 'EGP') }}
                            </div>
                            <small class="text-muted">
                                {{ __('Paid orders') }}: {{ $report['kpis']['paid_orders_count'] ?? 0 }}
                            </small>
                        </div>
                        <div class="text-success fs-4"><i class="bi bi-cash-stack"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Platform Commission (Earnings)') }}</div>
                            <div class="fs-4 fw-semibold">
                                {{ number_format($platformEarnings, 2) }} {{ setting('currency', 'EGP') }}
                            </div>
                        </div>
                        <div class="text-warning fs-4"><i class="bi bi-percent"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Net Revenue (after refunds)') }}</div>
                            <div class="fs-4 fw-semibold">
                                {{ number_format($netRevenue, 2) }} {{ setting('currency', 'EGP') }}
                            </div>
                            <small class="text-muted">
                                {{ __('Refunded total') }}:
                                {{ number_format($refundedTotal, 2) }} {{ setting('currency', 'EGP') }}
                            </small>
                        </div>
                        <div class="text-info fs-4"><i class="bi bi-graph-up"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Pending Vendor Withdrawals') }}</div>
                            <div class="fs-4 fw-semibold">{{ $report['kpis']['pending_withdrawals_count'] ?? 0 }}</div>
                            <small class="text-muted">
                                {{ number_format($report['kpis']['pending_withdrawals_total'] ?? 0, 2) }} {{ setting('currency', 'EGP') }}
                            </small>
                        </div>
                        <div class="text-primary fs-4"><i class="bi bi-cash-coin"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Daily Paid Revenue') }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($report['daily_sales'] as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td class="text-end">
                                {{ number_format($row['total'], 2) }} {{ setting('currency', 'EGP') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4">{{ __('No data.') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('modals')
        <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="filterOffcanvasLabel">{{ __('Filter Earnings') }}</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="offcanvas-body">
                <form method="GET" action="{{ route('admin.reports.earnings') }}" id="filterForm">
                    <div class="mb-3">
                        <label for="from_date" class="form-label">{{ __('From Date') }}</label>
                        <input type="date"
                               class="form-control"
                               id="from_date"
                               name="from_date"
                               value="{{ $filters['from_date'] ?? '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="to_date" class="form-label">{{ __('To Date') }}</label>
                        <input type="date"
                               class="form-control"
                               id="to_date"
                               name="to_date"
                               value="{{ $filters['to_date'] ?? '' }}">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search me-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('admin.reports.earnings') }}" class="btn btn-outline-secondary flex-fill">
                            <i class="bi bi-x-circle me-1"></i>{{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    @endpush
@endsection

