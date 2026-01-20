@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.app')

@section('title', 'Order Confirmed')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                     style="width: 100px; height: 100px;">
                    <i class="bi bi-check-lg" style="font-size: 3rem;"></i>
                </div>
                <h1 class="mb-3">Order Confirmed!</h1>
                <p class="lead text-muted">Thank you for your order. Your order has been received.</p>
            </div>
            
            <!-- Order Details Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
                        <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                    </div>
                    <span class="badge bg-{{ $order->status == 'pending' ? 'warning' : 'success' }} fs-6">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Store</h6>
                            <p class="mb-0">
                                <strong>{{ $order->store->name ?? 'Store' }}</strong><br>
                                @if($order->store->address)
                                    {{ $order->store->address }}<br>
                                @endif
                                @if($order->store->phone)
                                    <i class="bi bi-telephone me-1"></i>{{ $order->store->phone }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Payment</h6>
                            <p class="mb-0">
                                <strong>{{ ucfirst($order->payment_method) }}</strong><br>
                                Status: 
                                @if($order->payment_status == 'paid')
                                    <span class="text-success">Paid</span>
                                @else
                                    <span class="text-warning">{{ ucfirst($order->payment_status) }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <h6 class="text-muted mb-3">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->product_name }}
                                        @if($item->options)
                                            <br>
                                            <small class="text-muted">
                                                @foreach($item->options as $key => $value)
                                                    {{ ucfirst($key) }}: {{ $value }}@if(!$loop->last), @endif
                                                @endforeach
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">Subtotal</td>
                                    <td class="text-end">₹{{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">Tax</td>
                                    <td class="text-end">₹{{ number_format($order->tax, 2) }}</td>
                                </tr>
                                @if($order->discount > 0)
                                <tr>
                                    <td colspan="3" class="text-end">Discount</td>
                                    <td class="text-end text-success">-₹{{ number_format($order->discount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="text-end fs-5">₹{{ number_format($order->total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    @if($order->notes)
                    <div class="mt-3">
                        <h6 class="text-muted mb-2">Order Notes</h6>
                        <p class="mb-0">{{ $order->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- QR Code for Verification -->
            @if($order->hasQrCode())
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h5 class="mb-3">Order Verification QR Code</h5>
                    <div class="mb-3">
                        @if($order->hasQrCode())
                            <img src="{{ Storage::disk('public')->get($order->verification_qr_path) }}" 
                                 alt="Order QR Code" 
                                 class="img-fluid" 
                                 style="max-width: 300px;">
                        @else
                            <div class="alert alert-warning">QR Code not available</div>
                        @endif
                    </div>
                    <p class="mb-2 text-muted">Show this QR code when picking up your order</p>
                    <p class="mb-0 small text-muted">Order verification is secure and store-specific</p>
                </div>
            </div>
            @endif
            
            <!-- Actions -->
            <div class="d-flex justify-content-center gap-3">
                @auth
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-list-ul me-1"></i>View All Orders
                    </a>
                @endauth
                <a href="{{ route('store.show', $order->store->slug ?? '') }}" class="btn btn-primary">
                    <i class="bi bi-shop me-1"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

