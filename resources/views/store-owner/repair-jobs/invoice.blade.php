@extends('layouts.store-owner')

@section('title', 'Repair Invoice #' . $repairJob->ticket_number)
@section('page-title', 'Repair Invoice')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Invoice Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('store-owner.repair-jobs.show', $repairJob) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Job
                </a>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer me-1"></i>Print Invoice
                </button>
            </div>
        </div>

        <!-- Invoice Card -->
        <div class="card invoice-card">
            <div class="card-body p-5">
                <!-- Invoice Header -->
                <div class="row mb-4">
                    <div class="col-6">
                        <h2 class="text-primary mb-3">
                            <i class="bi bi-tools me-2"></i>{{ $store->name }}
                        </h2>
                        @if($store->address)
                        <p class="text-muted mb-1">{{ $store->address }}</p>
                        @endif
                        @if($store->phone)
                        <p class="text-muted mb-1"><i class="bi bi-telephone me-1"></i>{{ $store->phone }}</p>
                        @endif
                        @if($store->email)
                        <p class="text-muted mb-0"><i class="bi bi-envelope me-1"></i>{{ $store->email }}</p>
                        @endif
                    </div>
                    <div class="col-6 text-end">
                        <h3 class="mb-3">REPAIR INVOICE</h3>
                        <p class="mb-1"><strong>Invoice #:</strong> {{ $repairJob->ticket_number }}</p>
                        <p class="mb-1"><strong>Date:</strong> {{ now()->format('M d, Y') }}</p>
                        <p class="mb-0">
                            <strong>Status:</strong> 
                            <span class="badge bg-{{ $repairJob->status_color }}">{{ $repairJob->status_label }}</span>
                        </p>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Customer Info -->
                <div class="row mb-4">
                    <div class="col-6">
                        <h6 class="text-uppercase text-muted mb-3">Bill To:</h6>
                        @if($repairJob->customer)
                        <p class="mb-1"><strong>{{ $repairJob->customer->name }}</strong></p>
                        @if($repairJob->customer->phone)
                        <p class="mb-1">{{ $repairJob->customer->phone }}</p>
                        @endif
                        @if($repairJob->customer->email)
                        <p class="mb-1">{{ $repairJob->customer->email }}</p>
                        @endif
                        @if($repairJob->customer->address)
                        <p class="mb-0">{{ $repairJob->customer->address }}</p>
                        @endif
                        @else
                        <p class="text-muted">Walk-in Customer</p>
                        @endif
                    </div>
                    <div class="col-6">
                        <h6 class="text-uppercase text-muted mb-3">Device Details:</h6>
                        <p class="mb-1"><strong>{{ $repairJob->device_type_label }}</strong></p>
                        <p class="mb-1">{{ $repairJob->device_name }}</p>
                        @if($repairJob->imei_serial)
                        <p class="mb-1"><small>IMEI/Serial: {{ $repairJob->imei_serial }}</small></p>
                        @endif
                        @if($repairJob->device_color)
                        <p class="mb-0"><small>Color: {{ $repairJob->device_color }}</small></p>
                        @endif
                    </div>
                </div>

                <!-- Issue Description -->
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted mb-2">Issue Description:</h6>
                    <p class="mb-0">{{ $repairJob->issue_description }}</p>
                </div>

                <!-- Repair Details -->
                @if($repairJob->repair_notes)
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted mb-2">Repair Work Done:</h6>
                    <p class="mb-0">{{ $repairJob->repair_notes }}</p>
                </div>
                @endif

                <!-- Parts Used -->
                <h6 class="text-uppercase text-muted mb-3">Parts & Services</h6>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50%;">Description</th>
                            <th class="text-center" style="width: 15%;">Qty</th>
                            <th class="text-end" style="width: 17.5%;">Unit Price</th>
                            <th class="text-end" style="width: 17.5%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($repairJob->parts as $part)
                        <tr>
                            <td>{{ $part->product->name ?? $part->part_name ?? 'Spare Part' }}</td>
                            <td class="text-center">{{ $part->quantity }}</td>
                            <td class="text-end">₹{{ number_format($part->unit_price, 2) }}</td>
                            <td class="text-end">₹{{ number_format($part->total_price, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No parts used</td>
                        </tr>
                        @endforelse
                        
                        <!-- Service Charge Row -->
                        @php
                            $partsTotal = $repairJob->parts->sum('total_price');
                            $serviceCharge = ($repairJob->final_cost ?? $repairJob->estimated_cost ?? 0) - $partsTotal;
                        @endphp
                        @if($serviceCharge > 0)
                        <tr>
                            <td>Service / Labor Charges</td>
                            <td class="text-center">1</td>
                            <td class="text-end">₹{{ number_format($serviceCharge, 2) }}</td>
                            <td class="text-end">₹{{ number_format($serviceCharge, 2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="row">
                    <div class="col-6"></div>
                    <div class="col-6">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end" style="width: 120px;">₹{{ number_format($repairJob->final_cost ?? $repairJob->estimated_cost ?? 0, 2) }}</td>
                                </tr>
                                @if($repairJob->discount > 0)
                                <tr>
                                    <td class="text-end text-danger"><strong>Discount:</strong></td>
                                    <td class="text-end text-danger">-₹{{ number_format($repairJob->discount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="border-top">
                                    <td class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong>₹{{ number_format(($repairJob->final_cost ?? $repairJob->estimated_cost ?? 0) - ($repairJob->discount ?? 0), 2) }}</strong></td>
                                </tr>
                                @if($repairJob->advance_paid > 0)
                                <tr>
                                    <td class="text-end text-success"><strong>Advance Paid:</strong></td>
                                    <td class="text-end text-success">-₹{{ number_format($repairJob->advance_paid, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-end"><strong>Balance Due:</strong></td>
                                    <td class="text-end"><strong class="text-primary fs-5">₹{{ number_format($repairJob->balance_due, 2) }}</strong></td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Warranty Notice -->
                @if($repairJob->warranty_days > 0)
                <div class="alert alert-info mt-4">
                    <i class="bi bi-shield-check me-2"></i>
                    <strong>Warranty:</strong> This repair is covered under {{ $repairJob->warranty_days }}-day warranty
                    @if($repairJob->warranty_until)
                    (valid until {{ $repairJob->warranty_until->format('M d, Y') }})
                    @endif
                </div>
                @endif

                <!-- Footer -->
                <div class="text-center mt-5 pt-4 border-top">
                    <p class="text-muted mb-1">Thank you for choosing {{ $store->name }}!</p>
                    <p class="text-muted small mb-0">For any queries, please contact us with your ticket number.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn, nav, .sidebar, header, footer {
            display: none !important;
        }
        .invoice-card {
            border: none !important;
            box-shadow: none !important;
        }
        .col-lg-8 {
            max-width: 100% !important;
            flex: 0 0 100% !important;
        }
    }
</style>
@endsection
