<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Card - {{ $repairJob->ticket_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .job-card {
            max-width: 400px;
            margin: 10px auto;
            padding: 15px;
            border: 2px solid #000;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .shop-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .ticket-number {
            font-size: 24px;
            font-weight: bold;
            background: #000;
            color: #fff;
            padding: 5px 15px;
            display: inline-block;
            margin: 10px 0;
        }
        .section {
            margin-bottom: 12px;
        }
        .section-title {
            font-weight: bold;
            background: #f0f0f0;
            padding: 3px 8px;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px dotted #ccc;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .info-value {
            text-align: right;
        }
        .issue-box {
            border: 1px solid #ccc;
            padding: 8px;
            background: #fffef0;
            min-height: 50px;
        }
        .priority-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 3px;
        }
        .priority-low { background: #e5e7eb; }
        .priority-normal { background: #dbeafe; color: #1d4ed8; }
        .priority-high { background: #fef3c7; color: #d97706; }
        .priority-urgent { background: #fee2e2; color: #dc2626; }
        .footer {
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 15px;
            text-align: center;
        }
        .signature-line {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 5px;
            width: 60%;
            margin-left: auto;
            margin-right: auto;
            font-size: 10px;
        }
        .terms {
            font-size: 8px;
            color: #666;
            margin-top: 10px;
            text-align: left;
        }
        @media print {
            body { margin: 0; }
            .job-card { border: 2px solid #000; margin: 0; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; padding: 10px; background: #f0f0f0;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 14px; cursor: pointer;">
            🖨️ Print Job Card
        </button>
    </div>

    <div class="job-card">
        <div class="header">
            <div class="shop-name">{{ $store->name }}</div>
            @if($store->phone)
            <div>📞 {{ $store->phone }}</div>
            @endif
            @if($store->address)
            <div>{{ $store->address }}</div>
            @endif
            <div class="ticket-number">{{ $repairJob->ticket_number }}</div>
            <div>
                <span class="priority-badge priority-{{ $repairJob->priority }}">{{ $repairJob->priority_label }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Customer Details</div>
            @if($repairJob->customer)
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $repairJob->customer->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ $repairJob->customer->phone }}</span>
            </div>
            @else
            <div class="info-row">
                <span class="info-value">Walk-in Customer</span>
            </div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Device Information</div>
            <div class="info-row">
                <span class="info-label">Type:</span>
                <span class="info-value">{{ $repairJob->device_type_label }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Device:</span>
                <span class="info-value">{{ $repairJob->device_name }}</span>
            </div>
            @if($repairJob->imei_serial)
            <div class="info-row">
                <span class="info-label">IMEI/Serial:</span>
                <span class="info-value">{{ $repairJob->imei_serial }}</span>
            </div>
            @endif
            @if($repairJob->device_color)
            <div class="info-row">
                <span class="info-label">Color:</span>
                <span class="info-value">{{ $repairJob->device_color }}</span>
            </div>
            @endif
            @if($repairJob->device_accessories)
            <div class="info-row">
                <span class="info-label">Accessories:</span>
                <span class="info-value">{{ implode(', ', $repairJob->device_accessories) }}</span>
            </div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Issue Reported</div>
            <div class="issue-box">{{ $repairJob->issue_description }}</div>
        </div>

        <div class="section">
            <div class="section-title">Job Details</div>
            <div class="info-row">
                <span class="info-label">Received:</span>
                <span class="info-value">{{ $repairJob->created_at->format('d/m/Y h:i A') }}</span>
            </div>
            @if($repairJob->expected_delivery_at)
            <div class="info-row">
                <span class="info-label">Expected Delivery:</span>
                <span class="info-value">{{ $repairJob->expected_delivery_at->format('d/m/Y') }}</span>
            </div>
            @endif
            @if($repairJob->technician)
            <div class="info-row">
                <span class="info-label">Technician:</span>
                <span class="info-value">{{ $repairJob->technician->name }}</span>
            </div>
            @endif
            @if($repairJob->estimated_cost)
            <div class="info-row">
                <span class="info-label">Estimated Cost:</span>
                <span class="info-value">₹{{ number_format($repairJob->estimated_cost, 2) }}</span>
            </div>
            @endif
            @if($repairJob->advance_paid > 0)
            <div class="info-row">
                <span class="info-label">Advance Paid:</span>
                <span class="info-value">₹{{ number_format($repairJob->advance_paid, 2) }}</span>
            </div>
            @endif
        </div>

        <div class="footer">
            @if($repairJob->receivedBy)
            <div style="font-size: 10px; margin-bottom: 5px;">Received by: {{ $repairJob->receivedBy->name }}</div>
            @endif
            <div class="signature-line">Customer Signature</div>
            <div class="terms">
                <strong>Terms:</strong> 1. Collect device within 30 days. 2. We are not responsible for data loss. 
                3. Original receipt required for pickup. 4. Device password is stored securely.
                @if($repairJob->warranty_days > 0)
                5. {{ $repairJob->warranty_days }} days warranty on repair.
                @endif
            </div>
        </div>
    </div>
</body>
</html>
