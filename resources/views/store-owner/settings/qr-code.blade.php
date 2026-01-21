@extends('layouts.store-owner')

@section('title', 'Store QR Code')
@section('page-title', 'Store QR Code')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Your Store QR Code</h6>
            </div>
            <div class="card-body text-center">
                @if($store->qr_code && Storage::disk('public')->exists($store->qr_code))
                    <div class="mb-4">
                        <img src="{{ Storage::url($store->qr_code) }}" alt="Store QR Code" 
                             class="img-fluid" style="max-width: 300px;">
                    </div>
                    <p class="text-muted mb-4">
                        Customers can scan this QR code to access your store menu and place orders.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('store-owner.qr-code.download') }}" class="btn btn-primary">
                            <i class="bi bi-download me-1"></i> Download QR Code
                        </a>
                        <form action="{{ route('store-owner.qr-code.generate') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-clockwise me-1"></i> Regenerate
                            </button>
                        </form>
                    </div>
                @else
                    <div class="py-5">
                        <i class="bi bi-qr-code fs-1 text-muted d-block mb-3"></i>
                        <h5>No QR Code Generated</h5>
                        <p class="text-muted mb-4">
                            Generate a QR code for your store so customers can easily access your menu.
                        </p>
                        <form action="{{ route('store-owner.qr-code.generate') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-qr-code me-1"></i> Generate QR Code
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- Store URL -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Store URL</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Share this link with your customers:</p>
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ route('store.show', $store->slug) }}" 
                           id="storeUrl" readonly>
                    <button class="btn btn-outline-primary" type="button" onclick="copyUrl()">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
            </div>
        </div>

        <!-- Usage Tips -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">How to Use</h6>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li class="mb-2">Download and print the QR code</li>
                    <li class="mb-2">Display it at your store entrance, tables, or checkout counter</li>
                    <li class="mb-2">Customers scan the QR code with their phone camera</li>
                    <li class="mb-2">They can browse products and place orders directly</li>
                    <li>Use the POS Terminal to process counter payments</li>
                </ol>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyUrl() {
    const input = document.getElementById('storeUrl');
    input.select();
    document.execCommand('copy');
    
    // Show feedback
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
    setTimeout(() => {
        btn.innerHTML = originalHtml;
    }, 2000);
}
</script>
@endpush
@endsection
