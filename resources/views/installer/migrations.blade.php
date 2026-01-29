@extends('installer.layout')

@section('title', 'Migrations')
@section('subtitle', 'Step 4: Database Setup')
@section('show-steps', true)
@section('step1-class', 'completed')
@section('line1-class', 'completed')
@section('step2-class', 'completed')
@section('line2-class', 'completed')
@section('step3-class', 'completed')
@section('line3-class', 'completed')
@section('step4-class', 'active')

@section('content')
<div class="text-center mb-4">
    <i class="bi bi-database-fill-gear text-primary" style="font-size: 4rem;"></i>
    <h5 class="mt-3">Database Setup</h5>
    <p class="text-muted">
        We'll now create the database tables required for the POS system.
    </p>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">Tables to be created:</h6>
        <div class="row">
            <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-table text-primary me-2"></i>Users</li>
                    <li class="mb-2"><i class="bi bi-table text-primary me-2"></i>Stores</li>
                    <li class="mb-2"><i class="bi bi-table text-primary me-2"></i>Categories</li>
                    <li class="mb-2"><i class="bi bi-table text-primary me-2"></i>Products</li>
                    <li class="mb-2"><i class="bi bi-table text-primary me-2"></i>Orders</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-table text-primary me-2"></i>Order Items</li>
                    <li class="mb-2"><i class="bi bi-table text-primary me-2"></i>Carts</li>
                    <li class="mb-2"><i class="bi bi-table text-primary me-2"></i>Cart Items</li>
                    <li class="mb-2"><i class="bi bi-table text-primary me-2"></i>Payment Settings</li>
                    <li class="mb-2"><i class="bi bi-table text-primary me-2"></i>System Settings</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('installer.migrations.run') }}" method="POST">
    @csrf
    <div class="d-flex justify-content-between">
        <a href="{{ route('installer.database') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <button type="submit" class="btn btn-primary" id="runMigrations">
            Run Migrations <i class="bi bi-arrow-right ms-2"></i>
        </button>
    </div>
</form>

<script>
document.getElementById('runMigrations').closest('form').addEventListener('submit', function() {
    const btn = document.getElementById('runMigrations');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Running...';
});
</script>
@endsection
