@extends('installer.layout')

@section('title', 'Requirements')
@section('subtitle', 'Step 1: System Requirements')
@section('show-steps', true)
@section('step1-class', 'active')

@section('content')
<h5 class="mb-4">System Requirements Check</h5>

<!-- PHP Version -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>PHP Version</strong>
                <br>
                <small class="text-muted">Required: {{ $requirements['php']['required'] }} or higher</small>
            </div>
            <div class="text-end">
                <span class="badge {{ $requirements['php']['passed'] ? 'bg-success' : 'bg-danger' }}">
                    {{ $requirements['php']['current'] }}
                </span>
                @if($requirements['php']['passed'])
                    <i class="bi bi-check-circle text-success ms-2"></i>
                @else
                    <i class="bi bi-x-circle text-danger ms-2"></i>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- PHP Extensions -->
<div class="card mb-3">
    <div class="card-header">
        <strong>PHP Extensions</strong>
    </div>
    <div class="list-group list-group-flush">
        @foreach($requirements['extensions'] as $extension => $passed)
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <span>{{ $extension }}</span>
            @if($passed)
                <span class="text-success"><i class="bi bi-check-circle"></i> Installed</span>
            @else
                <span class="text-danger"><i class="bi bi-x-circle"></i> Missing</span>
            @endif
        </div>
        @endforeach
    </div>
</div>

<!-- Directory Permissions -->
<div class="card mb-4">
    <div class="card-header">
        <strong>Directory Permissions</strong>
    </div>
    <div class="list-group list-group-flush">
        @foreach($requirements['permissions'] as $directory => $writable)
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <span>{{ $directory }}</span>
            @if($writable)
                <span class="text-success"><i class="bi bi-check-circle"></i> Writable</span>
            @else
                <span class="text-danger"><i class="bi bi-x-circle"></i> Not Writable</span>
            @endif
        </div>
        @endforeach
    </div>
</div>

@if($allPassed)
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        All requirements passed! You can proceed with the installation.
    </div>
    <div class="d-grid">
        <a href="{{ route('installer.license') }}" class="btn btn-primary btn-lg">
            Continue <i class="bi bi-arrow-right ms-2"></i>
        </a>
    </div>
@else
    <div class="alert alert-danger">
        <i class="bi bi-x-circle me-2"></i>
        Some requirements are not met. Please fix them before continuing.
    </div>
    <div class="d-grid">
        <a href="{{ route('installer.requirements') }}" class="btn btn-outline-primary btn-lg">
            <i class="bi bi-arrow-clockwise me-2"></i>Re-Check Requirements
        </a>
    </div>
@endif
@endsection
