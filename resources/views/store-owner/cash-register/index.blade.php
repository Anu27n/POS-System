@extends('layouts.store-owner')

@section('title', 'Cash Register')
@section('page-title', 'Cash Register')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Current Session Card -->
        @if($currentSession)
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Current Active Session</h5>
                        <small>Opened by {{ $currentSession->staff?->name ?? 'Staff' }} at {{ $currentSession->opened_at->format('d M Y, h:i A') }}</small>
                    </div>
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#closeRegisterModal">
                        <i class="bi bi-lock me-1"></i> Close Register
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <h6 class="text-muted mb-1">Opening Cash</h6>
                                <h4 class="text-primary mb-0">{{ \App\Helpers\CurrencyHelper::format($currentSession->opening_cash) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <h6 class="text-muted mb-1">Cash Sales</h6>
                                <h4 class="text-success mb-0">{{ \App\Helpers\CurrencyHelper::format($currentSession->total_cash_sales) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <h6 class="text-muted mb-1">Card Sales</h6>
                                <h4 class="text-info mb-0">{{ \App\Helpers\CurrencyHelper::format($currentSession->total_card_sales) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded text-center">
                                <h6 class="text-muted mb-1">Expected Cash</h6>
                                <h4 class="text-success mb-0">{{ \App\Helpers\CurrencyHelper::format($currentSession->expected_cash) }}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Transactions -->
                    <div class="mt-4">
                        <h6 class="mb-3"><i class="bi bi-clock-history me-1"></i> Recent Transactions</h6>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th>Time</th>
                                        <th>Type</th>
                                        <th>Payment</th>
                                        <th>Reference</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($currentSession->transactions()->latest()->take(20)->get() as $transaction)
                                        <tr>
                                            <td class="text-muted">{{ $transaction->created_at->format('h:i A') }}</td>
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
                                                    <a href="#">Order #{{ $transaction->order_id }}</a>
                                                @else
                                                    {{ $transaction->notes ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold {{ $transaction->type === 'refund' || $transaction->type === 'cash_out' ? 'text-danger' : 'text-success' }}">
                                                {{ $transaction->type === 'refund' || $transaction->type === 'cash_out' ? '-' : '+' }}
                                                {{ \App\Helpers\CurrencyHelper::format($transaction->amount) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-3 text-muted">No transactions yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- No Active Session -->
            <div class="card mb-4">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-cash-coin text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h4>No Active Cash Register Session</h4>
                    <p class="text-muted mb-4">Start a new cash register session to begin tracking sales</p>
                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#openRegisterModal">
                        <i class="bi bi-unlock me-2"></i> Open Cash Register
                    </button>
                </div>
            </div>
        @endif

        <!-- Session History -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Session History</h5>
                <a href="{{ route('store-owner.cash-register.reports') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-bar-chart me-1"></i> View Reports
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Staff</th>
                                <th class="text-end">Opening</th>
                                <th class="text-end">Sales</th>
                                <th class="text-end">Closing</th>
                                <th class="text-center">Difference</th>
                                <th>Status</th>
                                <th width="80"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $session)
                                <tr>
                                    <td>
                                        <strong>{{ $session->opened_at->format('d M Y') }}</strong>
                                        <small class="text-muted d-block">{{ $session->opened_at->format('h:i A') }}</small>
                                    </td>
                                    <td>{{ $session->staff?->name ?? 'Staff' }}</td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($session->opening_cash) }}</td>
                                    <td class="text-end text-success">{{ \App\Helpers\CurrencyHelper::format($session->total_cash_sales + $session->total_card_sales) }}</td>
                                    <td class="text-end">{{ $session->closing_cash ? \App\Helpers\CurrencyHelper::format($session->closing_cash) : '-' }}</td>
                                    <td class="text-center">
                                        @if($session->closed_at)
                                            @php
                                                $diff = $session->closing_cash - $session->expected_cash;
                                            @endphp
                                            @if($diff == 0)
                                                <span class="badge bg-success">Balanced</span>
                                            @elseif($diff > 0)
                                                <span class="badge bg-info">+{{ \App\Helpers\CurrencyHelper::format($diff) }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ \App\Helpers\CurrencyHelper::format($diff) }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-warning">Open</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($session->closed_at)
                                            <span class="badge bg-secondary">Closed</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('store-owner.cash-register.show', $session) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                        No session history found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($sessions->hasPages())
                <div class="card-footer">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Today's Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Total Sessions</span>
                    <strong>{{ $todayStats['session_count'] }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Total Sales</span>
                    <strong class="text-success">{{ \App\Helpers\CurrencyHelper::format($todayStats['total_sales']) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Cash Sales</span>
                    <strong>{{ \App\Helpers\CurrencyHelper::format($todayStats['cash_sales']) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Card Sales</span>
                    <strong>{{ \App\Helpers\CurrencyHelper::format($todayStats['card_sales']) }}</strong>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        @if($currentSession)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#cashInModal">
                        <i class="bi bi-plus-circle me-1"></i> Cash In
                    </button>
                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#cashOutModal">
                        <i class="bi bi-dash-circle me-1"></i> Cash Out
                    </button>
                    <a href="{{ route('store-owner.pos.index') }}" class="btn btn-primary">
                        <i class="bi bi-display me-1"></i> Go to POS
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Open Register Modal -->
<div class="modal fade" id="openRegisterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('store-owner.cash-register.open') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-unlock me-2"></i>Open Cash Register</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Opening Cash Amount <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}</span>
                            <input type="number" step="0.01" class="form-control" name="opening_cash" placeholder="0.00" required autofocus>
                        </div>
                        <small class="text-muted">Enter the amount of cash in the register drawer</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Any notes about this session..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Open Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Register Modal -->
@if($currentSession)
<div class="modal fade" id="closeRegisterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('store-owner.cash-register.close', $currentSession) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-lock me-2"></i>Close Cash Register</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Opening Cash:</span>
                            <strong>{{ \App\Helpers\CurrencyHelper::format($currentSession->opening_cash) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Cash Sales:</span>
                            <strong>{{ \App\Helpers\CurrencyHelper::format($currentSession->total_cash_sales) }}</strong>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span>Expected Cash in Drawer:</span>
                            <strong class="text-primary">{{ \App\Helpers\CurrencyHelper::format($currentSession->expected_cash) }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Actual Closing Cash <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}</span>
                            <input type="number" step="0.01" class="form-control" name="closing_cash" 
                                   placeholder="{{ $currentSession->expected_cash }}" required autofocus>
                        </div>
                        <small class="text-muted">Count the cash in your drawer and enter the total</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Closing Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Any discrepancies or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-lock me-1"></i> Close Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cash In Modal -->
<div class="modal fade" id="cashInModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('store-owner.cash-register.add-cash', $currentSession) }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="cash_in">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Cash In</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}</span>
                            <input type="number" step="0.01" class="form-control" name="amount" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="notes" placeholder="e.g., Change added, Petty cash" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Cash</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cash Out Modal -->
<div class="modal fade" id="cashOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('store-owner.cash-register.add-cash', $currentSession) }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="cash_out">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-dash-circle me-2"></i>Cash Out</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}</span>
                            <input type="number" step="0.01" class="form-control" name="amount" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="notes" placeholder="e.g., Expense payment, Vendor payment" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Remove Cash</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(session('success'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div class="toast show" role="alert">
        <div class="toast-header bg-success text-white">
            <i class="bi bi-check-circle me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">{{ session('success') }}</div>
    </div>
</div>
@endif
@endsection
