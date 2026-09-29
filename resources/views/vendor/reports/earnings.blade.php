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
                    <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vendor.reports.index') }}">{{ __('Reports & Analytics') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Earnings') }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">{{ __('Earnings Dashboard') }}</h1>
            <p class="text-muted mb-0">{{ __('Your earnings and balance overview') }}</p>
        </div>
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
            <i class="bi bi-sliders me-1"></i>{{ __('Filters') }}
        </button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Current Balance') }}</div>
                            <div class="fs-4 fw-semibold">
                                {{ number_format($report['kpis']['current_balance'] ?? 0, 2) }} {{ setting('currency', 'EGP') }}
                            </div>
                        </div>
                        <div class="text-primary fs-4"><i class="bi bi-wallet2"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Net Earnings (period)') }}</div>
                            <div class="fs-4 fw-semibold">
                                {{ number_format($report['kpis']['net_earnings'] ?? 0, 2) }} {{ setting('currency', 'EGP') }}
                            </div>
                            <small class="text-muted">
                                {{ __('Gross Sales') }}: {{ number_format($report['kpis']['gross_sales'] ?? 0, 2) }}
                            </small>
                        </div>
                        <div class="text-success fs-4"><i class="bi bi-graph-up"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Approved Withdrawals (period)') }}</div>
                            <div class="fs-4 fw-semibold">
                                {{ number_format($report['kpis']['approved_withdrawals_total'] ?? 0, 2) }} {{ setting('currency', 'EGP') }}
                            </div>
                        </div>
                        <div class="text-success fs-4"><i class="bi bi-check2-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted">{{ __('Pending Withdrawals') }}</div>
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
            <h5 class="card-title mb-0">{{ __('Daily Paid Sales') }}</h5>
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
                <form method="GET" action="{{ route('vendor.reports.earnings') }}" id="filterForm">
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
                        <a href="{{ route('vendor.reports.earnings') }}" class="btn btn-outline-secondary flex-fill">
                            <i class="bi bi-x-circle me-1"></i>{{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    @endpush
@endsection

