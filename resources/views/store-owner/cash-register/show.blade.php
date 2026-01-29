@extends('layouts.store-owner')

@section('title', 'Cash Register Session')
@section('page-title', 'Session Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('store-owner.cash-register.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Cash Register
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Session Summary -->
        <div class="card mb-4 {{ $session->closed_at ? '' : 'border-success' }}">
            <div class="card-header {{ $session->closed_at ? 'bg-secondary' : 'bg-success' }} text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-cash-stack me-2"></i>
                        Cash Register Session
                        @if(!$session->closed_at)
                        <span class="badge bg-light text-success ms-2">Active</span>
                        @endif
                    </h5>
                    <small>{{ $session->opened_at->format('l, d M Y') }}</small>
                </div>
                @if($session->closed_at)
                <span class="badge bg-light text-secondary">Closed</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Session Information</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted">Staff:</td>
                                <td class="fw-semibold">{{ $session->staff?->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Opened At:</td>
                                <td>{{ $session->opened_at->format('d M Y, h:i A') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Closed At:</td>
                                <td>{{ $session->closed_at ? $session->closed_at->format('d M Y, h:i A') : 'Still Open' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Duration:</td>
                                <td>
                                    @if($session->closed_at)
                                    {{ $session->opened_at->diffForHumans($session->closed_at, true) }}
                                    @else
                                    {{ $session->opened_at->diffForHumans(now(), true) }} (ongoing)
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Notes</h6>
                        <div class="bg-light p-3 rounded">
                            <strong>Opening Notes:</strong>
                            <p class="mb-2">{{ $session->notes ?? 'No notes' }}</p>
                            @if($session->closing_notes)
                            <strong>Closing Notes:</strong>
                            <p class="mb-0">{{ $session->closing_notes }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="p-3 bg-primary bg-opacity-10 rounded text-center">
                            <h6 class="text-muted mb-1">Opening Cash</h6>
                            <h4 class="text-primary mb-0">{{ \App\Helpers\CurrencyHelper::format($session->opening_cash) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-success bg-opacity-10 rounded text-center">
                            <h6 class="text-muted mb-1">Total Sales</h6>
                            <h4 class="text-success mb-0">{{ \App\Helpers\CurrencyHelper::format($session->total_cash_sales + $session->total_card_sales + $session->total_upi_sales) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 {{ $session->closed_at ? 'bg-secondary' : 'bg-warning' }} bg-opacity-10 rounded text-center">
                            <h6 class="text-muted mb-1">{{ $session->closed_at ? 'Closing Cash' : 'Expected Cash' }}</h6>
                            <h4 class="{{ $session->closed_at ? 'text-secondary' : 'text-warning' }} mb-0">
                                {{ \App\Helpers\CurrencyHelper::format($session->closed_at ? $session->closing_cash : $session->expected_cash) }}
                            </h4>
                        </div>
                    </div>
                </div>

                @if($session->closed_at)
                <!-- Reconciliation -->
                @php
                $difference = $session->closing_cash - $session->expected_cash;
                @endphp
                <div class="mt-4">
                    <div class="alert {{ $difference == 0 ? 'alert-success' : ($difference > 0 ? 'alert-info' : 'alert-danger') }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Cash Reconciliation</strong>
                                <p class="mb-0">
                                    @if($difference == 0)
                                    <i class="bi bi-check-circle me-1"></i> Cash drawer is balanced perfectly!
                                    @elseif($difference > 0)
                                    <i class="bi bi-arrow-up-circle me-1"></i> Cash over by {{ \App\Helpers\CurrencyHelper::format($difference) }}
                                    @else
                                    <i class="bi bi-arrow-down-circle me-1"></i> Cash short by {{ \App\Helpers\CurrencyHelper::format(abs($difference)) }}
                                    @endif
                                </p>
                            </div>
                            <div class="fs-3 fw-bold">
                                @if($difference >= 0)
                                +{{ \App\Helpers\CurrencyHelper::format($difference) }}
                                @else
                                {{ \App\Helpers\CurrencyHelper::format($difference) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- All Transactions -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Transactions</h5>
                <span class="badge bg-secondary">{{ $session->transactions->count() }} transactions</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Payment Method</th>
                                <th>Reference</th>
                                <th>Notes</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($session->transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('h:i A') }}</td>
                                <td>
                                    @if($transaction->type === 'sale')
                                    <span class="badge bg-success">Sale</span>
                                    @elseif($transaction->type === 'refund')
                                    <span class="badge bg-danger">Refund</span>
                                    @elseif($transaction->type === 'cash_in')
                                    <span class="badge bg-info">Cash In</span>
                                    @else
                                    <span class="badge bg-warning">Cash Out</span>
                                    @endif
                                </td>
                                <td class="text-capitalize">{{ $transaction->payment_method }}</td>
                                <td>
                                    @if($transaction->order_id)
                                    <a href="{{ route('store-owner.orders.show', $transaction->order_id) }}" class="text-decoration-none">
                                        Order #{{ $transaction->order_id }}
                                    </a>
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>{{ $transaction->notes ?? '-' }}</td>
                                <td class="text-end fw-semibold {{ $transaction->type === 'refund' || $transaction->type === 'cash_out' ? 'text-danger' : 'text-success' }}">
                                    {{ $transaction->type === 'refund' || $transaction->type === 'cash_out' ? '-' : '+' }}
                                    {{ \App\Helpers\CurrencyHelper::format($transaction->amount) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No transactions in this session
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Sales Breakdown -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Sales Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="bi bi-cash text-success me-2"></i>Cash Sales</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($session->total_cash_sales) }}</strong>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ ($session->total_cash_sales + $session->total_card_sales + $session->total_upi_sales) > 0 ? ($session->total_cash_sales / ($session->total_cash_sales + $session->total_card_sales + $session->total_upi_sales)) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="bi bi-credit-card text-primary me-2"></i>Card Sales</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($session->total_card_sales) }}</strong>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: {{ ($session->total_cash_sales + $session->total_card_sales + $session->total_upi_sales) > 0 ? ($session->total_card_sales / ($session->total_cash_sales + $session->total_card_sales + $session->total_upi_sales)) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="bi bi-phone text-info me-2"></i>UPI Sales</span>
                        <strong>{{ \App\Helpers\CurrencyHelper::format($session->total_upi_sales) }}</strong>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: {{ ($session->total_cash_sales + $session->total_card_sales + $session->total_upi_sales) > 0 ? ($session->total_upi_sales / ($session->total_cash_sales + $session->total_card_sales + $session->total_upi_sales)) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <strong>Total Sales</strong>
                    <strong class="text-success fs-5">{{ \App\Helpers\CurrencyHelper::format($session->total_cash_sales + $session->total_card_sales + $session->total_upi_sales) }}</strong>
                </div>
            </div>
        </div>

        <!-- Cash Flow Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Cash Flow</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td>Opening Cash</td>
                        <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($session->opening_cash) }}</td>
                    </tr>
                    <tr class="text-success">
                        <td>+ Cash Sales</td>
                        <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($session->total_cash_sales) }}</td>
                    </tr>
                    <tr class="text-info">
                        <td>+ Cash In</td>
                        <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($session->total_cash_in ?? 0) }}</td>
                    </tr>
                    <tr class="text-danger">
                        <td>- Cash Out</td>
                        <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($session->total_cash_out ?? 0) }}</td>
                    </tr>
                    <tr class="table-primary fw-bold">
                        <td>= Actual Closing Cash (Auto)</td>
                        <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($session->expected_cash) }}</td>
                    </tr>
                </table>
                @if($session->closed_at)
                <div class="alert alert-success mt-3 mb-0">
                    <i class="bi bi-check-circle me-1"></i>
                    <strong>Session Closed:</strong> The closing cash was automatically calculated to prevent discrepancies.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection