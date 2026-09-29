@extends('layouts.app')

@php
    $page = 'withdrawals';
@endphp

@section('title', __('Withdrawals'))

@section('content')
    <div class="container-fluid p-4 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Withdrawals') }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">{{ __('Withdrawals') }}</h1>
                <p class="text-muted mb-0">{{ __('Request payout from your vendor balance') }}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Current Balance') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="fs-4 fw-semibold mb-2">
                            {{ number_format($vendor->balance, 2) }} {{ setting('currency', 'EGP') }}
                        </div>
                        <small class="text-muted">{{ __('Available for withdrawal') }}</small>
                    </div>
                </div>

                @if(vendorCan('create-withdrawals'))
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('New Withdrawal Request') }}</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('vendor.withdrawals.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">{{ __('Amount') }}</label>
                                <input type="number"
                                       name="amount"
                                       step="0.01"
                                       min="0.01"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount') }}"
                                       required>
                                @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Method') }}</label>
                                <input type="text"
                                       name="method"
                                       class="form-control @error('method') is-invalid @enderror"
                                       value="{{ old('method') }}"
                                       placeholder="{{ __('e.g., Bank Transfer, PayPal') }}">
                                @error('method')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Notes') }}</label>
                                <textarea name="notes"
                                          class="form-control @error('notes') is-invalid @enderror"
                                          rows="2"
                                          placeholder="{{ __('Optional notes') }}">{{ old('notes') }}</textarea>
                                @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-cash-coin me-1"></i>{{ __('Submit Request') }}
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Withdrawal Requests') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Method') }}</th>
                                    <th>{{ __('Requested At') }}</th>
                                    <th>{{ __('Processed At') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($withdrawals as $withdrawal)
                                    <tr>
                                        <td>{{ $withdrawal->id }}</td>
                                        <td>{{ number_format($withdrawal->amount, 2) }} {{ setting('currency', 'EGP') }}</td>
                                        <td>
                                            @php $status = $withdrawal->status; @endphp
                                            <span class="badge
                                                @if($status === 'pending') bg-warning text-dark
                                                @elseif($status === 'approved') bg-success
                                                @else bg-danger @endif">
                                                {{ __($status) }}
                                            </span>
                                        </td>
                                        <td>{{ $withdrawal->method ?? '-' }}</td>
                                        <td><small class="text-muted">{{ optional($withdrawal->created_at)->format('Y-m-d H:i') }}</small></td>
                                        <td><small class="text-muted">{{ optional($withdrawal->processed_at)->format('Y-m-d H:i') ?? '-' }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">{{ __('No withdrawals found.') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            {{ $withdrawals->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

