<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Repair - RepairDesk Pro</title>
    
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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -25%;
            width: 80%;
            height: 150%;
            background: radial-gradient(ellipse, rgba(102, 126, 234, 0.12) 0%, transparent 60%);
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -30%;
            right: -20%;
            width: 60%;
            height: 100%;
            background: radial-gradient(ellipse, rgba(6, 182, 212, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 10;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 20px 40px rgba(6, 182, 212, 0.3);
        }

        .brand-logo i {
            font-size: 2.5rem;
            color: white;
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 8px;
        }

        .brand-subtitle {
            color: #64748b;
            font-size: 1.1rem;
        }

        .tracking-card {
            background: var(--dark-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 48px;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 10;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
        }

        .tracking-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-green));
            border-radius: 0 0 4px 4px;
        }

        .form-label {
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .form-control {
            background: var(--dark-bg);
            border: 2px solid var(--border-color);
            color: white;
            padding: 16px 20px;
            border-radius: 14px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: var(--dark-bg);
            border-color: var(--accent-cyan);
            color: white;
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.15);
        }

        .form-control::placeholder {
            color: #475569;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: 1.2rem;
        }

        .input-icon .form-control {
            padding-left: 52px;
        }

        .optional-badge {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
            margin-left: 8px;
        }

        .btn-track {
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
            border: none;
            color: white;
            padding: 18px 32px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1.1rem;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 15px 30px rgba(6, 182, 212, 0.25);
        }

        .btn-track:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(6, 182, 212, 0.35);
            color: white;
        }

        .helper-text {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
        }

        .helper-text p {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 12px;
        }

        .helper-text a {
            color: var(--accent-cyan);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .helper-text a:hover {
            color: var(--accent-green);
        }

        .back-link {
            position: absolute;
            top: 30px;
            left: 30px;
            color: #64748b;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: color 0.2s;
            z-index: 20;
        }

        .back-link:hover {
            color: white;
        }

        .alert {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
    </style>
</head>
<body>
    <a href="{{ route('home') }}" class="back-link">
        <i class="bi bi-arrow-left"></i>
        Back to Home
    </a>

    <div class="brand-header">
        <div class="brand-logo">
            <i class="bi bi-search"></i>
        </div>
        <h1 class="brand-title">Track Your Repair</h1>
        <p class="brand-subtitle">Enter your ticket number to check repair status</p>
    </div>
    
    <div class="tracking-card">
        @if($errors->any())
        <div class="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ $errors->first() }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}
        </div>
        @endif
        
        <form action="{{ route('track.search') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="form-label">Ticket Number</label>
                <div class="input-icon">
                    <i class="bi bi-ticket-detailed"></i>
                    <input type="text" 
                           name="ticket_number" 
                           class="form-control" 
                           placeholder="e.g. REP-2026-0451"
                           value="{{ old('ticket_number') }}"
                           required
                           autofocus>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label">
                    Phone Number
                    <span class="optional-badge">(Optional - Last 4 digits)</span>
                </label>
                <div class="input-icon">
                    <i class="bi bi-phone"></i>
                    <input type="text" 
                           name="phone" 
                           class="form-control" 
                           placeholder="For additional verification"
                           value="{{ old('phone') }}"
                           maxlength="4">
                </div>
            </div>
            
            <button type="submit" class="btn-track">
                <i class="bi bi-search"></i>
                Track My Repair
            </button>
        </form>
        
        <div class="helper-text">
            <p>Can't find your ticket number?</p>
            <a href="#">Contact support for assistance</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
