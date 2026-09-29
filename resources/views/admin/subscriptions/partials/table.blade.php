@if($subscriptions->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Vendor') }}</th>
                    <th>{{ __('Plan') }}</th>
                    <th>{{ __('Start Date') }}</th>
                    <th>{{ __('End Date') }}</th>
                    <th>{{ __('Price') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subscriptions as $subscription)
                    <tr>
                        <td>
                            <strong>{{ $subscription->vendor->getTranslation('name', app()->getLocale()) }}</strong>
                        </td>
                        <td>
                            <strong>{{ $subscription->plan->getTranslation('name', app()->getLocale()) }}</strong>
                        </td>
                        <td>
                            <strong>{{ $subscription->start_date }}</strong>
                        </td>
                        <td>
                            <strong>{{ $subscription->end_date }}</strong>
                        </td>
                        <td>
                            <strong>{{ $subscription->price }}</strong>
                        </td>
                        <td>
                            @if($subscription->status === 'active')
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                            @endif
                        </td>


                        <td>
                            <small class="text-muted">{{ $subscription->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.subscriptions.show', $subscription) }}"
                                    class="btn btn-outline-info" title="{{ __('View') }}">
                                    <i class="bi bi-eye m-0"></i>
                                </a>

                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-5">
        <i class="bi bi-box-seam display-1 text-muted"></i>
        <p class="text-muted mt-3">{{ __('No subscriptions found.') }}</p>
    </div>
@endif
