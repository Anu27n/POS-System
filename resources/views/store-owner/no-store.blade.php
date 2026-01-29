@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-shop-window display-1 text-muted"></i>
                    </div>
                    
                    <h3 class="mb-3">No Store Found</h3>
                    
                    @if(auth()->user()->isStoreOwner())
                        <p class="text-muted mb-4">
                            You don't have a store yet. Please create one to start using the POS system.
                        </p>
                        <a href="{{ route('store-owner.stores.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Create Your Store
                        </a>
                    @elseif(auth()->user()->isStaff())
                        <p class="text-muted mb-4">
                            You are not assigned to any store. Please contact your store owner to assign you to a store.
                        </p>
                        <a href="{{ route('home') }}" class="btn btn-outline-primary">
                            <i class="bi bi-house me-2"></i>Go to Home
                        </a>
                    @else
                        <p class="text-muted mb-4">
                            You don't have access to the store owner dashboard.
                        </p>
                        <a href="{{ route('home') }}" class="btn btn-outline-primary">
                            <i class="bi bi-house me-2"></i>Go to Home
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
