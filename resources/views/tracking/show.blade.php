<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repair Status: {{ $repairJob->ticket_number }} - RepairDesk Pro</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --gradient-start: #667eea;
            --gradient-end: #764ba2;
            --accent-cyan: #06b6d4;
            --accent-green: #10b981;
            --accent-orange: #f59e0b;
            --dark-bg: #0a0e1a;
            --dark-card: #141b2d;
            --border-color: rgba(255, 255, 255, 0.08);
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            background: var(--dark-bg);
            min-height: 100vh;
            padding: 40px 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -25%;
            width: 80%;
            height: 150%;
            background: radial-gradient(ellipse, rgba(102, 126, 234, 0.1) 0%, transparent 60%);
            pointer-events: none;
        }

        .container {
            max-width: 900px;
            position: relative;
            z-index: 10;
        }

        .back-link {
            color: #64748b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            margin-bottom: 32px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: white;
        }

        /* Header Card */
        .status-header {
            background: var(--dark-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 24px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .status-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-green), var(--gradient-start));
        }

        .ticket-number {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(6, 182, 212, 0.1);
            border: 1px solid rgba(6, 182, 212, 0.3);
            color: var(--accent-cyan);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 32px;
            border-radius: 16px;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .status-received { background: rgba(100, 116, 139, 0.2); color: #94a3b8; }
        .status-diagnosed { background: rgba(6, 182, 212, 0.2); color: var(--accent-cyan); }
        .status-waiting_approval { background: rgba(245, 158, 11, 0.2); color: var(--accent-orange); }
        .status-waiting_parts { background: rgba(245, 158, 11, 0.2); color: var(--accent-orange); }
        .status-in_progress { background: rgba(102, 126, 234, 0.2); color: var(--gradient-start); }
        .status-repaired { background: rgba(16, 185, 129, 0.2); color: var(--accent-green); }
        .status-ready_pickup { background: rgba(16, 185, 129, 0.3); color: var(--accent-green); }
        .status-delivered { background: rgba(30, 41, 59, 0.5); color: #94a3b8; }
        .status-cancelled { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .status-unrepairable { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

        .device-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
        }

        .issue-desc {
            color: #64748b;
            font-size: 1rem;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .info-card {
            background: var(--dark-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
        }

        .info-card i {
            font-size: 1.8rem;
            margin-bottom: 12px;
        }

        .info-card.cyan i { color: var(--accent-cyan); }
        .info-card.green i { color: var(--accent-green); }
        .info-card.orange i { color: var(--accent-orange); }
        .info-card.purple i { color: var(--gradient-start); }

        .info-card .label {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 4px;
        }

        .info-card .value {
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
        }

        /* Timeline */
        .timeline-card {
            background: var(--dark-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 24px;
        }

        .card-title {
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-title i {
            color: var(--accent-cyan);
        }

        .timeline {
            position: relative;
            padding-left: 36px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 12px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-color);
        }

        .timeline-item {
            position: relative;
            padding-bottom: 28px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -30px;
            top: 4px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .timeline-dot.active {
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
            color: white;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.4);
        }

        .timeline-dot.completed {
            background: rgba(16, 185, 129, 0.2);
            color: var(--accent-green);
        }

        .timeline-dot.pending {
            background: rgba(100, 116, 139, 0.2);
            color: #64748b;
        }

        .timeline-content h6 {
            color: white;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .timeline-content p {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 4px;
        }

        .timeline-content small {
            color: #475569;
            font-size: 0.8rem;
        }

        /* Contact Card */
        .contact-card {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: 24px;
            padding: 32px;
            text-align: center;
        }

        .contact-card h5 {
            color: white;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .contact-card p {
            color: #94a3b8;
            margin-bottom: 20px;
        }

        .contact-info {
            display: flex;
            justify-content: center;
            gap: 32px;
            flex-wrap: wrap;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        .contact-item i {
            width: 40px;
            height: 40px;
            background: rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gradient-start);
        }

        .contact-item a {
            color: white;
            text-decoration: none;
        }

        .contact-item a:hover {
            color: var(--accent-cyan);
        }

        /* Delivery Alert */
        .delivery-alert {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(6, 182, 212, 0.15));
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            margin-bottom: 24px;
        }

        .delivery-alert i {
            font-size: 2rem;
            color: var(--accent-green);
            margin-bottom: 12px;
        }

        .delivery-alert h5 {
            color: white;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .delivery-alert p {
            color: #94a3b8;
            margin: 0;
        }

        .delivery-date {
            color: var(--accent-green);
            font-weight: 700;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('track.index') }}" class="back-link">
            <i class="bi bi-arrow-left"></i>
            Track Another Repair
        </a>

        <!-- Status Header -->
        <div class="status-header">
            <div class="ticket-number">
                <i class="bi bi-ticket-detailed"></i>
                {{ $repairJob->ticket_number }}
            </div>
            
            @php
                $statusInfo = \App\Models\RepairJob::STATUSES[$repairJob->status] ?? ['label' => 'Unknown', 'icon' => 'question'];
            @endphp
            
            <div class="status-badge status-{{ $repairJob->status }}">
                <i class="bi bi-{{ $statusInfo['icon'] }}"></i>
                {{ $statusInfo['label'] }}
            </div>
            
            <h2 class="device-name">
                {{ $repairJob->device_brand ?? '' }} {{ $repairJob->device_model ?? $repairJob->device_type_label }}
            </h2>
            <p class="issue-desc">{{ Str::limit($repairJob->issue_description, 100) }}</p>
        </div>

        <!-- Ready for Pickup Alert -->
        @if($repairJob->status === 'ready_pickup')
        <div class="delivery-alert">
            <i class="bi bi-gift"></i>
            <h5>Your device is ready for pickup!</h5>
            <p>Please visit the store during business hours to collect your device.</p>
        </div>
        @elseif($repairJob->expected_delivery_at && !in_array($repairJob->status, ['delivered', 'cancelled', 'unrepairable']))
        <div class="delivery-alert">
            <i class="bi bi-calendar-check"></i>
            <h5>Expected Delivery</h5>
            <p class="delivery-date">{{ $repairJob->expected_delivery_at->format('l, F j, Y') }}</p>
            <p>{{ $repairJob->expected_delivery_at->format('g:i A') }}</p>
        </div>
        @endif

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-card cyan">
                <i class="bi bi-calendar-event"></i>
                <div class="label">Received On</div>
                <div class="value">{{ $repairJob->created_at->format('M d, Y') }}</div>
            </div>
            
            <div class="info-card green">
                <i class="bi bi-{{ $repairJob->getDeviceTypeIcon() }}"></i>
                <div class="label">Device Type</div>
                <div class="value">{{ $repairJob->device_type_label }}</div>
            </div>
            
            <div class="info-card orange">
                <i class="bi bi-flag"></i>
                <div class="label">Priority</div>
                <div class="value">{{ ucfirst($repairJob->priority) }}</div>
            </div>
            
            @if($repairJob->warranty_days > 0)
            <div class="info-card purple">
                <i class="bi bi-shield-check"></i>
                <div class="label">Warranty</div>
                <div class="value">{{ $repairJob->warranty_days }} Days</div>
            </div>
            @endif
        </div>

        <!-- Timeline -->
        <div class="timeline-card">
            <h5 class="card-title">
                <i class="bi bi-clock-history"></i>
                Repair Timeline
            </h5>
            
            <div class="timeline">
                @foreach($repairJob->statusLogs->sortByDesc('created_at') as $index => $log)
                @php
                    $logStatus = \App\Models\RepairJob::STATUSES[$log->new_status] ?? ['label' => 'Unknown', 'icon' => 'circle'];
                @endphp
                <div class="timeline-item">
                    <div class="timeline-dot {{ $index === 0 ? 'active' : 'completed' }}">
                        <i class="bi bi-{{ $logStatus['icon'] }}"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>{{ $logStatus['label'] }}</h6>
                        @if($log->notes)
                        <p>{{ $log->notes }}</p>
                        @endif
                        <small>{{ $log->created_at->format('M d, Y \a\t g:i A') }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Contact Card -->
        <div class="contact-card">
            <h5>{{ $repairJob->store->name ?? 'Repair Shop' }}</h5>
            <p>Have questions about your repair?</p>
            
            <div class="contact-info">
                @if($repairJob->store->phone ?? false)
                <div class="contact-item">
                    <i class="bi bi-telephone-fill"></i>
                    <a href="tel:{{ $repairJob->store->phone }}">{{ $repairJob->store->phone }}</a>
                </div>
                @endif
                
                @if($repairJob->store->email ?? false)
                <div class="contact-item">
                    <i class="bi bi-envelope-fill"></i>
                    <a href="mailto:{{ $repairJob->store->email }}">{{ $repairJob->store->email }}</a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
