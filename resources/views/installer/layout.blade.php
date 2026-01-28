<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Installation') - POS System Installer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .installer-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
        }
        .installer-header {
            background: #667eea;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .installer-header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        .installer-body {
            padding: 30px;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .step {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 5px;
            background: #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .step-line {
            width: 30px;
            height: 2px;
            background: #e9ecef;
            align-self: center;
        }
        .step-line.completed {
            background: #28a745;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <i class="bi bi-shop fs-1 mb-2 d-block"></i>
            <h1>POS System Installer</h1>
            <p class="mb-0 opacity-75">@yield('subtitle', 'Setup your multi-store ordering system')</p>
        </div>
        
        @hasSection('show-steps')
        <div class="bg-light py-3">
            <div class="step-indicator">
                <div class="step @yield('step1-class', '')">1</div>
                <div class="step-line @yield('line1-class', '')"></div>
                <div class="step @yield('step2-class', '')">2</div>
                <div class="step-line @yield('line2-class', '')"></div>
                <div class="step @yield('step3-class', '')">3</div>
                <div class="step-line @yield('line3-class', '')"></div>
                <div class="step @yield('step4-class', '')">4</div>
                <div class="step-line @yield('line4-class', '')"></div>
                <div class="step @yield('step5-class', '')">5</div>
            </div>
        </div>
        @endif
        
        <div class="installer-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
