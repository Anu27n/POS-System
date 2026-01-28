<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $appSettings['app_name'] ?? 'POS System') - {{ $appSettings['app_name'] ?? 'POS System' }}</title>

    @if(!empty($appSettings['app_favicon']))
    <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['app_favicon']) }}">
    @endif

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #030a22;
            --primary-light: #0a1940;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }

        /* Global image fallback placeholder */
        .image-fallback {
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 2rem;
        }

        .navbar {
            background-color: var(--primary-color) !important;
        }

        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border-radius: 0.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        footer {
            background-color: var(--primary-color) !important;
            color: white;
        }

        footer p {
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* ============================
           MOBILE RESPONSIVE STYLES
           ============================ */
        
        @media (max-width: 768px) {
            /* Navbar mobile */
            .navbar-brand img {
                height: 28px !important;
            }
            .navbar-brand {
                font-size: 1rem;
            }
            
            /* Container padding */
            .container {
                padding-left: 12px;
                padding-right: 12px;
            }
            
            /* Cards mobile */
            .card {
                margin-bottom: 1rem;
            }
            .card-header {
                padding: 0.75rem 1rem;
            }
            .card-header h5 {
                font-size: 1rem;
            }
            .card-body {
                padding: 1rem;
            }
            
            /* Buttons mobile */
            .btn-lg {
                font-size: 1rem;
                padding: 0.6rem 1.2rem;
            }
            
            /* Form controls mobile */
            .form-control, .form-select {
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            /* Payment options mobile */
            .payment-option .card-body {
                padding: 0.75rem;
            }
            .payment-option .card-body i {
                font-size: 2rem !important;
            }
            .payment-option .card-body .fw-semibold {
                font-size: 0.9rem;
            }
            .payment-option .card-body .small {
                font-size: 0.75rem;
            }
            
            /* Cart item mobile */
            .d-flex.align-items-center.p-3 {
                flex-wrap: wrap;
                padding: 0.75rem !important;
            }
            .d-flex.align-items-center.p-3 > .me-3 img,
            .d-flex.align-items-center.p-3 > .me-3 > div {
                width: 60px !important;
                height: 60px !important;
            }
            .d-flex.align-items-center.p-3 > .text-end {
                width: 100%;
                text-align: right;
                margin-top: 0.5rem;
                margin-left: 0 !important;
            }
            
            /* Order summary mobile */
            .sticky-top {
                position: relative !important;
                top: 0 !important;
            }
            
            /* Modal mobile */
            .modal-dialog {
                margin: 0.5rem;
            }
            .modal-body {
                padding: 1rem;
            }
            
            /* Store header mobile */
            .store-header {
                padding: 1rem 0 !important;
            }
            .store-header .row {
                flex-direction: column;
                text-align: center;
            }
            .store-header .col-auto {
                margin-bottom: 0.75rem;
            }
            .store-header h1 {
                font-size: 1.5rem;
            }
            .store-header .col {
                margin-bottom: 0.75rem;
            }
            
            /* Stripe card element mobile */
            #card-element {
                font-size: 14px;
            }
        }
        
        @media (max-width: 575px) {
            /* Smaller screens */
            .py-4 {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }
            .py-5 {
                padding-top: 1.5rem !important;
                padding-bottom: 1.5rem !important;
            }
            h1 {
                font-size: 1.5rem;
            }
            .fs-5 {
                font-size: 1rem !important;
            }
            
            /* Product grid mobile */
            .col-md-4, .col-lg-3, .col-md-6 {
                padding-left: 6px;
                padding-right: 6px;
            }
            
            /* Cart quantity buttons */
            .btn-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.8rem;
            }
            
            /* Store category sidebar mobile */
            .col-lg-3 {
                margin-bottom: 1rem;
            }
            .list-group-item {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            /* Payment page mobile */
            .col-md-6 {
                padding: 0;
            }
            
            /* Navbar dropdown */
            .dropdown-menu {
                position: static !important;
                transform: none !important;
                width: 100%;
            }
        }
        
        /* Prevent horizontal overflow */
        html, body {
            overflow-x: hidden;
            max-width: 100vw;
        }
        
        .container, .container-fluid {
            overflow-x: hidden;
        }
        
        /* Stripe Elements mobile fix */
        .StripeElement {
            box-sizing: border-box;
            width: 100%;
        }
        
        #card-element {
            width: 100%;
            min-height: 44px;
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                @if(!empty($appSettings['app_logo']))
                    <img src="{{ asset('storage/' . $appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'POS System' }}" style="height: 35px;" class="me-2">
                @else
                    <i class="bi bi-qr-code-scan me-2"></i>
                @endif
                {{ $appSettings['app_name'] ?? 'POS System' }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pricing') ? 'active' : '' }}" href="{{ route('pricing') }}">Pricing</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm px-3 ms-2" href="{{ route('register') }}">Get Started</a>
                    </li>
                    @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if(auth()->user()->isAdmin())
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
                            @endif
                            @if(auth()->user()->isStoreOwner() || auth()->user()->isStaff())
                            <li><a class="dropdown-item" href="{{ route('store-owner.dashboard') }}"><i class="bi bi-shop me-2"></i>Store Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('store-owner.pos.index') }}"><i class="bi bi-display me-2"></i>POS Terminal</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('cart.index') }}"><i class="bi bi-cart me-2"></i>My Cart</a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="bi bi-bag me-2"></i>My Orders</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container mt-3" style="position: absolute; z-index: 1050; width: 100%;">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global image error handler - replaces broken images with SVG placeholder
        document.addEventListener('DOMContentLoaded', function() {
            // Find all images without explicit onerror handler
            document.querySelectorAll('img').forEach(function(img) {
                if (!img.hasAttribute('onerror') || img.getAttribute('onerror') === '') {
                    img.onerror = function() {
                        this.onerror = null; // Prevent infinite loop
                        const width = this.width || 200;
                        const height = this.height || 200;
                        this.src = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='${width}' height='${height}'%3E%3Crect fill='%23f3f4f6' width='${width}' height='${height}'/%3E%3Ctext fill='%239ca3af' font-family='sans-serif' font-size='24' dy='10.5' font-weight='bold' x='50%25' y='50%25' text-anchor='middle'%3E📦%3C/text%3E%3C/svg%3E`;
                    };
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>

</html>