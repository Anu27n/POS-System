@extends('layouts.app')

@section('title', 'Pricing Plans')

@section('content')
<div class="container py-5">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold mb-3">Simple, Transparent Pricing</h1>
        <p class="lead text-muted mx-auto" style="max-width: 600px;">
            Choose the perfect plan for your business. Start with a free trial and upgrade anytime.
        </p>
    </div>

    <!-- Pricing Cards -->
    <div class="row g-4 justify-content-center mb-5">
        @forelse($plans as $plan)
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 {{ $plan->is_popular ? 'border-primary border-2 shadow-lg' : 'shadow-sm' }}">
                    @if($plan->is_popular)
                        <div class="card-header bg-primary text-white text-center py-2">
                            <small class="fw-semibold text-uppercase">Most Popular</small>
                        </div>
                    @endif
                    <div class="card-body p-4 text-center">
                        <h3 class="fw-bold mb-2">{{ $plan->name }}</h3>
                        @if($plan->description)
                            <p class="text-muted small mb-3">{{ $plan->description }}</p>
                        @endif
                        
                        <div class="my-4">
                            <span class="display-4 fw-bold">₹{{ number_format($plan->price, 0) }}</span>
                            <span class="text-muted">/{{ str_replace('ly', '', $plan->billing_cycle) }}</span>
                        </div>

                        @if($plan->trial_days > 0)
                            <div class="alert alert-success py-2 mb-3">
                                <small><i class="bi bi-gift me-1"></i> {{ $plan->trial_days }}-day free trial</small>
                            </div>
                        @endif

                        <hr>

                        <ul class="list-unstyled text-start mb-4">
                            @foreach($plan->features ?? [] as $featureSlug)
                                @php $feature = $features->flatten()->firstWhere('slug', $featureSlug); @endphp
                                @if($feature)
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        {{ $feature->name }}
                                    </li>
                                @endif
                            @endforeach
                            @if(empty($plan->features))
                                <li class="text-muted text-center">No features listed</li>
                            @endif
                        </ul>

                        @auth
                            @if(auth()->user()->isStoreOwner())
                                <a href="{{ route('pricing.checkout', $plan) }}" class="btn btn-{{ $plan->is_popular ? 'primary' : 'outline-primary' }} w-100 btn-lg">
                                    Get Started
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary w-100 btn-lg">
                                    Register as Store Owner
                                </a>
                            @endif
                        @else
                            <a href="{{ route('register') }}" class="btn btn-{{ $plan->is_popular ? 'primary' : 'outline-primary' }} w-100 btn-lg">
                                Get Started
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-credit-card-2-front display-1 text-muted mb-3 d-block"></i>
                <h4 class="text-muted">No pricing plans available yet</h4>
                <p class="text-muted">Please check back later.</p>
            </div>
        @endforelse
    </div>

    <!-- Feature Comparison (Optional) -->
    @if($plans->count() > 1)
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-center">Feature Comparison</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Feature</th>
                            @foreach($plans as $plan)
                                <th class="text-center py-3">{{ $plan->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $categorySlug => $categoryName)
                            @php $categoryFeatures = $features->get($categorySlug, collect()); @endphp
                            @if($categoryFeatures->count() > 0)
                                <tr class="table-secondary">
                                    <td colspan="{{ $plans->count() + 1 }}" class="fw-semibold text-uppercase small py-2">
                                        {{ $categoryName }}
                                    </td>
                                </tr>
                                @foreach($categoryFeatures as $feature)
                                    <tr>
                                        <td>
                                            {{ $feature->name }}
                                            @if($feature->description)
                                                <small class="text-muted d-block">{{ $feature->description }}</small>
                                            @endif
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if(in_array($feature->slug, $plan->features ?? []))
                                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                @else
                                                    <i class="bi bi-x-circle text-muted fs-5"></i>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- FAQ Section -->
    <div class="mt-5 pt-4">
        <h3 class="text-center mb-4">Frequently Asked Questions</h3>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Can I change my plan later?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! You can upgrade or downgrade your plan at any time. When you upgrade, you'll be charged the prorated difference. When you downgrade, the change takes effect at the end of your current billing cycle.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept all major credit cards, debit cards, UPI, and net banking through our secure payment partners Razorpay and Stripe.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Is there a free trial?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, many of our plans come with a free trial period. Check the plan details to see the trial duration. No credit card required to start your trial.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Can I cancel my subscription?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, you can cancel your subscription at any time from your dashboard. Your access will continue until the end of your current billing period.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
