<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Store Dashboard') - {{ $appSettings['app_name'] ?? 'POS System' }}</title>

    @if(!empty($appSettings['app_favicon']))
    <link rel="icon" type="image/png" href="{{ asset('storage/' . $appSettings['app_favicon']) }}">
    @endif

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #030a22;
            --primary-dark: #020818;
            --primary-light: #0a1940;
            --accent-color: #ffffff;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            padding-top: 0;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar-brand:hover {
            color: white;
        }

        .sidebar-brand i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-nav .nav-link {
            color: var(--text-muted);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.05);
            border-left-color: rgba(255, 255, 255, 0.3);
        }

        .sidebar-nav .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }

        .sidebar-nav .nav-link i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-nav .nav-section {
            color: var(--text-muted);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 1.5rem 1.5rem 0.5rem;
            font-weight: 600;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .top-navbar {
            background: white;
            padding: 1rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid #e5e7eb;
        }

        .content-wrapper {
            padding: 1.5rem;
        }

        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border-radius: 0.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
        }

        .stat-card {
            border-left: 4px solid var(--primary-color);
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-card .stat-label {
            color: #64748b;
            font-size: 0.875rem;
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

        .table th {
            font-weight: 600;
            color: #475569;
            border-bottom-width: 1px;
            background-color: #f8fafc;
        }

        .badge-role {
            background-color: var(--primary-color);
            color: white;
        }

        .user-dropdown {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            transition: background-color 0.2s;
        }

        .user-dropdown:hover {
            background-color: #f1f5f9;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('store-owner.dashboard') }}" class="sidebar-brand">
                <i class="bi bi-shop"></i>
                <span>{{ auth()->user()->getEffectiveStore()->name ?? 'My Store' }}</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">Main</div>
            <a href="{{ route('store-owner.dashboard') }}" class="nav-link {{ request()->routeIs('store-owner.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <div class="nav-section">Catalog</div>
            @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('manage_categories'))
            <a href="{{ route('store-owner.categories.index') }}" class="nav-link {{ request()->routeIs('store-owner.categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Categories
            </a>
            @endif
            @if(auth()->user()->isStoreOwner() || auth()->user()->hasAnyStaffPermission(['manage_products', 'manage_inventory']))
            <a href="{{ route('store-owner.products.index') }}" class="nav-link {{ request()->routeIs('store-owner.products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Products
            </a>
            @endif

            <div class="nav-section">Sales</div>
            @if(auth()->user()->isStoreOwner() || auth()->user()->hasAnyStaffPermission(['view_orders', 'manage_orders']))
            <a href="{{ route('store-owner.orders.index') }}" class="nav-link {{ request()->routeIs('store-owner.orders.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Orders
            </a>
            @endif
            @if(auth()->user()->isStoreOwner() || auth()->user()->hasAnyStaffPermission(['use_pos', 'process_payments']))
            <a href="{{ route('store-owner.pos.index') }}" class="nav-link {{ request()->routeIs('store-owner.pos.*') ? 'active' : '' }}">
                <i class="bi bi-qr-code-scan"></i> POS Terminal
            </a>
            @endif

            <div class="nav-section">People</div>
            @if(auth()->user()->isStoreOwner() || auth()->user()->hasAnyStaffPermission(['view_customers', 'manage_customers']))
            <a href="{{ route('store-owner.customers.index') }}" class="nav-link {{ request()->routeIs('store-owner.customers.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Customers
            </a>
            @endif
            @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('manage_staff'))
            <a href="{{ route('store-owner.staff.index') }}" class="nav-link {{ request()->routeIs('store-owner.staff.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Staff
            </a>
            @endif

            <div class="nav-section">Cash Management</div>
            @if(auth()->user()->isStoreOwner() || auth()->user()->hasAnyStaffPermission(['use_pos', 'process_payments']))
            <a href="{{ route('store-owner.cash-register.index') }}" class="nav-link {{ request()->routeIs('store-owner.cash-register.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Cash Register
            </a>
            @endif

            <div class="nav-section">Reports</div>
            @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('view_reports'))
            <a href="{{ route('store-owner.reports.sales') }}" class="nav-link {{ request()->routeIs('store-owner.reports.sales') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Sales Report
            </a>
            <a href="{{ route('store-owner.reports.inventory') }}" class="nav-link {{ request()->routeIs('store-owner.reports.inventory') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i> Inventory Report
            </a>
            <a href="{{ route('store-owner.reports.tax') }}" class="nav-link {{ request()->routeIs('store-owner.reports.tax') ? 'active' : '' }}">
                <i class="bi bi-percent"></i> Tax Report
            </a>
            @endif

            <div class="nav-section">Settings</div>
            @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('manage_settings'))
            <a href="{{ route('store-owner.settings.index') }}" class="nav-link {{ request()->routeIs('store-owner.settings.index') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Store Settings
            </a>
            @if(auth()->user()->getEffectiveStore()->hasFeature('store_customization'))
            <a href="{{ route('store-owner.customization.index') }}" class="nav-link {{ request()->routeIs('store-owner.customization.*') ? 'active' : '' }}">
                <i class="bi bi-palette"></i> Customization
            </a>
            @endif
            <a href="{{ route('store-owner.tax-settings.index') }}" class="nav-link {{ request()->routeIs('store-owner.tax-settings.*') ? 'active' : '' }}">
                <i class="bi bi-receipt-cutoff"></i> Tax Settings
            </a>
            <a href="{{ route('store-owner.payment-settings.index') }}" class="nav-link {{ request()->routeIs('store-owner.payment-settings.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Payment Settings
            </a>
            <a href="{{ route('store-owner.qr-code.index') }}" class="nav-link {{ request()->routeIs('store-owner.qr-code.*') ? 'active' : '' }}">
                <i class="bi bi-qr-code"></i> Store QR Code
            </a>
            @endif
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-dark d-md-none me-2" type="button" onclick="document.querySelector('.sidebar').classList.toggle('show')">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h5 class="mb-0 fw-semibold">@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                @php $effectiveStore = auth()->user()->getEffectiveStore(); @endphp
                @if($effectiveStore)
                @if(auth()->user()->isStoreOwner() || auth()->user()->hasAnyStaffPermission(['use_pos', 'process_payments']))
                <a href="{{ route('store-owner.pos.index') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-display me-1"></i>POS
                </a>
                @endif
                <a href="{{ route('store.show', $effectiveStore->slug) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                    <i class="bi bi-eye me-1"></i>View Store
                </a>
                @endif
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle user-dropdown" data-bs-toggle="dropdown">
                        <div class="me-2 text-end d-none d-sm-block">
                            <div class="fw-semibold">{{ auth()->user()->name }}</div>
                            <small class="text-muted">{{ auth()->user()->isStoreOwner() ? 'Store Owner' : (auth()->user()->staffProfile->role_name ?? 'Staff') }}</small>
                        </div>
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('home') }}"><i class="bi bi-house me-2"></i>Home</a></li>
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
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content-wrapper">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global image error handler
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('img').forEach(function(img) {
                if (!img.hasAttribute('onerror') || img.getAttribute('onerror') === '') {
                    img.onerror = function() {
                        this.onerror = null;
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