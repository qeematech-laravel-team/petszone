@extends('layouts.app')

@section('title', 'Plans')

@section('content')
    <div class="container py-5">

        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="fw-bold">Our Plans</h1>
                <p class="text-muted">Choose the plan that fits your needs. Upgrade, downgrade, or cancel anytime.</p>
            </div>
        </div>

        {{-- Plans cards --}}
        <div class="row g-4">
            @foreach ($plans as $plan)
                <div class="col-md-4">
                    <div
                        class="card h-100 shadow-sm rounded-3
                        @if ($plan->is_featured) border-primary border-3 featured-card @else border-light @endif
                        hover-shadow transition">

                        <div class="card-body text-center p-4">

                            {{-- Featured Badge --}}
                            @if ($plan->is_featured)
                                <span class="badge bg-primary position-absolute top-0 translate-middle-x mt-3"
                                    style="left:15%">
                                    {{ __('Featured') }}
                                </span>
                            @endif

                            {{-- Plan Name --}}
                            <h5 class="card-title fw-bold mt-4">{{ $plan->getTranslation('name', app()->getLocale()) }}</h5>

                            {{-- Price --}}
                            <h6 class="card-price text-primary my-3 display-6">
                                {{ $plan->price }}<span class="text-muted fs-6">/{{ $plan->duration_days }} days</span>
                            </h6>

                            {{-- Features --}}
                            <ul class=" mb-4 text-start">
                                <li class="mt-3"> {{ $plan->max_products_count ?? __('Unlimited') }} Products</li>
                                <li class="mt-3"> {{ $plan->can_feature_products ? 'Featured' : 'Unfeatured' }} Products
                                </li>
                                <li class="mt-3"> {{ $plan->getTranslation('description', app()->getLocale()) }}</li>
                            </ul>

                            {{-- Choose Button --}}
                            <a href="#"
                                class="btn w-100 subscribe-button
                                @if ($plan->is_featured) btn-primary @else btn-outline-primary @endif
                                fw-bold py-2"
                                data-plan-id="{{ $plan->id }}">
                                {{ __('Choose Plan') }}
                            </a>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const subscribeButtons = document.querySelectorAll('.subscribe-button');

            subscribeButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();

                    const planId = button.getAttribute('data-plan-id');


                    fetch("{{ route('vendor.plans.check') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ plan_id: planId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        // Check if user has active subscription
                        const hasActiveSubscription = data.has_active_subscription || false;
                        const isDowngrade = data.is_downgrade || false;

                        let message = "Are you sure you want to subscribe to this plan?";
                        let showImmediateOption = false;

                        if (hasActiveSubscription && isDowngrade) {
                            showImmediateOption = true;
                            message = "You currently have an active subscription. Choose when to switch to the new plan:";
                        }

                        if (!data.can_feature_products && data.featured_count > 0 && setting('profit_type') == 'subscription') {
                            message += `\n\nWarning: This plan does NOT support featured products. ${data.featured_count} of your products are currently featured and will be set as unfeatured.`;
                        }

                        if (data.max_products_count && data.current_products > data.max_products_count && setting('profit_type') == 'subscription') {
                            message += `\n\nWarning: You currently have ${data.current_products} active products but this plan allows only ${data.max_products_count}. You must delete or deactivate some products before subscribing.`;
                        }

                        // Show confirmation dialog with immediate option if downgrade
                        if (showImmediateOption) {
                            Swal.fire({
                                title: 'Confirm Subscription',
                                html: `
                                    <p>${message}</p>
                                    <div class="mt-3">
                                        <label class="form-check">
                                            <input type="radio" name="switchType" value="immediate" class="form-check-input" checked>
                                            <span class="form-check-label">
                                                <strong class="text-dark">Switch Immediately</strong><br>
                                                <small class="text-dark">Cancel current subscription and start new one now</small>
                                            </span>
                                        </label>
                                        <label class="form-check mt-2">
                                            <input type="radio" name="switchType" value="scheduled" class="form-check-input">
                                            <span class="form-check-label">
                                                <strong class="text-dark">Wait Until Current Subscription Ends</strong><br>
                                                <small class="text-dark">Keep current benefits until paid period ends</small>
                                            </span>
                                        </label>
                                    </div>
                                `,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Subscribe',
                                cancelButtonText: 'Cancel',
                                reverseButtons: true,
                                didOpen: () => {
                                    // Make radio buttons work
                                    const radios = Swal.getContainer().querySelectorAll('input[type="radio"]');
                                    radios.forEach(radio => {
                                        radio.addEventListener('change', function() {
                                            radios.forEach(r => r.checked = false);
                                            this.checked = true;
                                        });
                                    });
                                },
                                preConfirm: () => {
                                    const selected = Swal.getContainer().querySelector('input[name="switchType"]:checked');
                                    return selected ? selected.value : 'immediate';
                                }
                            }).then((result) => {
                                if (result.isConfirmed && result.value) {
                                    const immediate = result.value === 'immediate';

                                    fetch("{{ route('vendor.plans.subscribe') }}", {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({
                                            plan_id: planId,
                                            immediate: immediate
                                        })
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            const switchType = immediate ? 'immediately' : 'scheduled';
                                            Swal.fire(
                                                'Subscribed!',
                                                `Your subscription has been confirmed and will ${switchType === 'immediately' ? 'start immediately' : 'start after your current subscription ends'}.`,
                                                'success'
                                            ).then(() => {
                                                location.reload();
                                            });
                                        } else {
                                            Swal.fire(
                                                'Error!',
                                                data.message || 'Something went wrong.',
                                                'error'
                                            );
                                        }
                                    })
                                    .catch(err => {
                                        Swal.fire(
                                            'Error!',
                                            'Something went wrong.',
                                            'error'
                                        );
                                    });
                                }
                            });
                        } else {
                            // No active subscription or upgrade - simple confirmation
                            Swal.fire({
                                title: 'Confirm Subscription',
                                text: message,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, subscribe',
                                cancelButtonText: 'Cancel',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetch("{{ route('vendor.plans.subscribe') }}", {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({
                                            plan_id: planId,
                                            immediate: true // Default to immediate for new subscriptions or upgrades
                                        })
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire(
                                                'Subscribed!',
                                                'Your subscription has been confirmed.',
                                                'success'
                                            ).then(() => {
                                                location.reload();
                                            });
                                        } else {
                                            Swal.fire(
                                                'Error!',
                                                data.message || 'Something went wrong.',
                                                'error'
                                            );
                                        }
                                    })
                                    .catch(err => {
                                        Swal.fire(
                                            'Error!',
                                            'Something went wrong.',
                                            'error'
                                        );
                                    });
                                }
                            });
                        }
                    });
                });
            });
        });

    </script>
@endpush
