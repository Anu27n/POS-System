@extends('installer.layout')

@section('title', 'Welcome')
@section('subtitle', 'Welcome to the installation wizard')

@section('content')
<div class="text-center mb-4">
    <h2 class="h4 mb-3">Welcome to POS System</h2>
    <p class="text-muted">
        This wizard will guide you through the installation process. 
        Please make sure you have the following information ready:
    </p>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">Before you begin, you'll need:</h6>
        <ul class="list-unstyled mb-0">
            <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                Database credentials (MySQL, SQLite, or PostgreSQL)
            </li>
            <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                Admin account details (name, email, password)
            </li>
            <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                Application name for your POS system
            </li>
        </ul>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    The installation will take approximately 2-3 minutes.
</div>

<div class="d-grid">
    <a href="{{ route('installer.requirements') }}" class="btn btn-primary btn-lg">
        Let's Get Started <i class="bi bi-arrow-right ms-2"></i>
    </a>
</div>
@endsection
