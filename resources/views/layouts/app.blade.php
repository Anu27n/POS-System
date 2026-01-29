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
        
        /* Interactive User Popup Menu Styles */
        .user-menu-container {
            position: relative;
        }
        
        .user-menu-trigger {
            cursor: pointer;
            padding: 8px 12px !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .user-menu-trigger:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .user-menu-trigger.active {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .user-menu-trigger .chevron-icon {
            font-size: 0.75rem;
            transition: transform 0.3s ease;
        }
        
        .user-menu-trigger.active .chevron-icon {
            transform: rotate(180deg);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-popup-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 320px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px) scale(0.98);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 9999;
            overflow: hidden;
        }
        
        .user-popup-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }
        
        .user-popup-menu::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 24px;
            width: 16px;
            height: 16px;
            background: #fff;
            transform: rotate(45deg);
            border-radius: 3px;
            box-shadow: -2px -2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .popup-header {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        
        .popup-header h6 {
            color: #fff;
        }
        
        .popup-header small {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        
        .popup-avatar {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        
        .popup-body {
            padding: 12px;
        }
        
        .popup-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none;
            color: #333;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }
        
        .popup-item:hover {
            background: #f5f7fa;
            transform: translateX(4px);
            color: #333;
        }
        
        .popup-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        
        .popup-text {
            flex: 1;
            margin-left: 12px;
        }
        
        .popup-text span {
            display: block;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .popup-text small {
            color: #888;
            font-size: 0.75rem;
        }
        
        .popup-item > .bi-chevron-right {
            color: #ccc;
            font-size: 0.8rem;
            transition: all 0.2s ease;
        }
        
        .popup-item:hover > .bi-chevron-right {
            color: #667eea;
            transform: translateX(3px);
        }
        
        .popup-footer {
            padding: 12px;
            border-top: 1px solid #eee;
            background: #fafafa;
        }
        
        .popup-logout-btn {
            width: 100%;
            padding: 12px;
            border: none;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            color: #fff;
            border-radius: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .popup-logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .user-popup-menu {
                width: 290px;
                right: -10px;
            }
            
            .user-popup-menu::before {
                right: 20px;
            }
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
                <ul class="navbar-nav ms-auto align-items-center">
                    @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm px-3 ms-2" href="{{ route('register') }}">Get Started</a>
                    </li>
                    @else
                    <!-- Interactive User Menu -->
                    <li class="nav-item position-relative user-menu-container">
                        <a class="nav-link user-menu-trigger d-flex align-items-center" href="javascript:void(0);" id="userMenuTrigger">
                            <div class="user-avatar me-2">
                                <i class="bi bi-person-circle fs-4"></i>
                            </div>
                            <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                            <i class="bi bi-chevron-down ms-1 chevron-icon"></i>
                        </a>
                        
                        <!-- Popup Menu -->
                        <div class="user-popup-menu" id="userPopupMenu">
                            <div class="popup-header">
                                <div class="d-flex align-items-center">
                                    <div class="popup-avatar">
                                        <i class="bi bi-person-circle fs-1"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                                        <small class="text-muted">{{ auth()->user()->email }}</small>
                                        <span class="badge bg-{{ auth()->user()->isAdmin() ? 'danger' : (auth()->user()->isStoreOwner() ? 'primary' : (auth()->user()->isStaff() ? 'info' : 'secondary')) }} ms-1">
                                            {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="popup-body">
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="popup-item">
                                    <div class="popup-icon bg-danger bg-opacity-10 text-danger">
                                        <i class="bi bi-speedometer2"></i>
                                    </div>
                                    <div class="popup-text">
                                        <span>Admin Dashboard</span>
                                        <small>Manage system settings</small>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                                @endif
                                
                                @if(auth()->user()->isStoreOwner() || auth()->user()->isStaff())
                                <a href="{{ route('store-owner.dashboard') }}" class="popup-item">
                                    <div class="popup-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-shop"></i>
                                    </div>
                                    <div class="popup-text">
                                        <span>Store Dashboard</span>
                                        <small>Manage your store</small>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                                <a href="{{ route('store-owner.pos.index') }}" class="popup-item">
                                    <div class="popup-icon bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-display"></i>
                                    </div>
                                    <div class="popup-text">
                                        <span>POS Terminal</span>
                                        <small>Process sales</small>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                                @endif
                                
                                <a href="{{ route('cart.index') }}" class="popup-item">
                                    <div class="popup-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-cart3"></i>
                                    </div>
                                    <div class="popup-text">
                                        <span>My Cart</span>
                                        <small>View cart items</small>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                                
                                <a href="{{ route('orders.index') }}" class="popup-item">
                                    <div class="popup-icon bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-bag"></i>
                                    </div>
                                    <div class="popup-text">
                                        <span>My Orders</span>
                                        <small>Track your orders</small>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                            
                            <div class="popup-footer">
                                <form action="{{ route('logout') }}" method="POST" class="d-block">
                                    @csrf
                                    <button type="submit" class="popup-logout-btn">
                                        <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container mt-3">
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
        // Interactive User Popup Menu
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuTrigger = document.getElementById('userMenuTrigger');
            const userPopupMenu = document.getElementById('userPopupMenu');
            
            if (userMenuTrigger && userPopupMenu) {
                // Toggle menu on click
                userMenuTrigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const isOpen = userPopupMenu.classList.contains('show');
                    
                    if (isOpen) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                });
                
                // Close menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userMenuTrigger.contains(e.target) && !userPopupMenu.contains(e.target)) {
                        closeMenu();
                    }
                });
                
                // Close menu on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeMenu();
                    }
                });
                
                // Add hover effect for menu items
                const popupItems = userPopupMenu.querySelectorAll('.popup-item');
                popupItems.forEach(function(item) {
                    item.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateX(4px)';
                    });
                    item.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateX(0)';
                    });
                });
                
                function openMenu() {
                    userPopupMenu.classList.add('show');
                    userMenuTrigger.classList.add('active');
                }
                
                function closeMenu() {
                    userPopupMenu.classList.remove('show');
                    userMenuTrigger.classList.remove('active');
                }
            }
        });
        
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