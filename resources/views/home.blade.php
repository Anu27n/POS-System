@extends('layouts.app')

@section('title', 'Modern POS System - Simplify Your Business Operations')

@section('content')
<style>
    /* ============================================
   MODERN POS - PREMIUM LANDING PAGE
   ============================================ */

    :root {
        --primary-dark: #0f172a;
        --primary-light: #1e293b;
        --accent: #3b82f6;
        --accent-hover: #2563eb;
        --success: #10b981;
        --warning: #f59e0b;
        --text-primary: #334155;
        --text-muted: #64748b;
        --bg-light: #f8fafc;
    }

    /* Hero Section */
    .hero-modern {
        min-height: 100vh;
        background: var(--primary-dark);
        position: relative;
        overflow: hidden;
        padding-top: 0px;
        margin-top: -1px; /* Remove any gap between navbar and hero */
    }

    .hero-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background:
            radial-gradient(ellipse 80% 50% at 50% -20%, rgba(59, 130, 246, 0.3), transparent),
            radial-gradient(ellipse 60% 40% at 100% 100%, rgba(139, 92, 246, 0.2), transparent);
        pointer-events: none;
    }

    .hero-grid {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 60px 60px;
        mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, black 20%, transparent 70%);
    }

    .hero-content {
        position: relative;
        z-index: 10;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(59, 130, 246, 0.15);
        border: 1px solid rgba(59, 130, 246, 0.3);
        color: #60a5fa;
        padding: 10px 20px;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 28px;
        animation: fadeInUp 0.6s ease;
    }

    .hero-title {
        font-size: clamp(2.5rem, 6vw, 4rem);
        font-weight: 800;
        line-height: 1.1;
        color: white;
        margin-bottom: 24px;
        letter-spacing: -0.02em;
        animation: fadeInUp 0.6s ease 0.1s both;
    }

    .hero-title .highlight {
        background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 50%, #f472b6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-description {
        font-size: 1.25rem;
        color: #94a3b8;
        max-width: 520px;
        margin-bottom: 36px;
        line-height: 1.7;
        animation: fadeInUp 0.6s ease 0.2s both;
    }

    .hero-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 48px;
        animation: fadeInUp 0.6s ease 0.3s both;
    }

    .btn-glow {
        background: var(--accent);
        color: white;
        padding: 16px 32px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 0 40px rgba(59, 130, 246, 0.4);
        text-decoration: none;
    }

    .btn-glow:hover {
        background: var(--accent-hover);
        transform: translateY(-3px);
        box-shadow: 0 0 60px rgba(59, 130, 246, 0.6);
        color: white;
    }

    .btn-ghost {
        background: transparent;
        color: white;
        padding: 16px 32px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-ghost:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.4);
        color: white;
    }

    .hero-trust {
        display: flex;
        align-items: center;
        gap: 32px;
        flex-wrap: wrap;
        animation: fadeInUp 0.6s ease 0.4s both;
    }

    .trust-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-size: 0.9rem;
    }

    .trust-item i {
        color: var(--success);
        font-size: 1.1rem;
    }

    /* Dashboard Preview */
    .hero-visual {
        position: relative;
        z-index: 10;
        animation: fadeInRight 0.8s ease 0.3s both;
    }

    .dashboard-preview {
        position: relative;
        background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 24px;
        box-shadow:
            0 25px 50px -12px rgba(0, 0, 0, 0.5),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }

    .dashboard-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .dashboard-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .dashboard-dot.red {
        background: #ef4444;
    }

    .dashboard-dot.yellow {
        background: #f59e0b;
    }

    .dashboard-dot.green {
        background: #10b981;
    }

    .dashboard-title {
        margin-left: 12px;
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .dashboard-content {
        background: rgba(15, 23, 42, 0.5);
        border-radius: 16px;
        padding: 24px;
    }

    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.03);
        border-radius: 14px;
        padding: 18px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        background: rgba(255, 255, 255, 0.05);
        transform: translateY(-2px);
    }

    .stat-label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
    }

    .stat-value.green {
        color: #10b981;
    }

    .stat-value.blue {
        color: #3b82f6;
    }

    .chart-visual {
        background: rgba(255, 255, 255, 0.02);
        border-radius: 12px;
        padding: 16px;
    }

    .chart-label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 12px;
    }

    .chart-bars {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        height: 100px;
    }

    .chart-bar {
        flex: 1;
        background: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%);
        border-radius: 6px 6px 0 0;
        min-height: 15px;
        transition: all 0.3s ease;
    }

    .chart-bar:hover {
        filter: brightness(1.2);
    }

    .chart-bar:nth-child(1) {
        height: 45%;
    }

    .chart-bar:nth-child(2) {
        height: 68%;
    }

    .chart-bar:nth-child(3) {
        height: 42%;
    }

    .chart-bar:nth-child(4) {
        height: 85%;
    }

    .chart-bar:nth-child(5) {
        height: 58%;
    }

    .chart-bar:nth-child(6) {
        height: 95%;
    }

    .chart-bar:nth-child(7) {
        height: 72%;
    }

    /* Floating Notification Cards */
    .floating-card {
        position: absolute;
        background: white;
        border-radius: 16px;
        padding: 16px 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        animation: float 6s ease-in-out infinite;
        z-index: 20;
    }

    .floating-card.card-1 {
        top: 5%;
        right: -20px;
        animation-delay: 0s;
    }

    .floating-card.card-2 {
        bottom: 15%;
        left: -30px;
        animation-delay: 2s;
    }

    .floating-card.card-3 {
        bottom: 40%;
        right: -40px;
        animation-delay: 4s;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-12px);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(40px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .floating-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .floating-icon.green {
        background: #dcfce7;
        color: #16a34a;
    }

    .floating-icon.blue {
        background: #dbeafe;
        color: #2563eb;
    }

    .floating-icon.orange {
        background: #ffedd5;
        color: #ea580c;
    }

    .floating-content {
        margin-left: 14px;
    }

    .floating-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 2px;
    }

    .floating-subtitle {
        font-size: 0.75rem;
        color: #64748b;
    }

    /* Payment Methods Strip */
    .payment-strip {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 20px 32px;
        margin-top: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
    }

    .payment-label {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .payment-icons {
        display: flex;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .payment-icon {
        width: 50px;
        height: 32px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-weight: 600;
        font-size: 0.75rem;
        flex-shrink: 0;
    }
    
    /* Payment strip mobile */
    @media (max-width: 768px) {
        .payment-strip {
            padding: 15px 20px;
            margin-top: 30px;
            gap: 15px;
            flex-direction: column;
        }
        .payment-label {
            font-size: 0.8rem;
            text-align: center;
        }
        .payment-icons {
            gap: 12px;
        }
        .payment-icon {
            width: 45px;
            height: 28px;
            font-size: 0.65rem;
        }
    }
    
    @media (max-width: 480px) {
        .payment-strip {
            padding: 12px 15px;
            margin-top: 20px;
            gap: 10px;
        }
        .payment-icons {
            gap: 8px;
        }
        .payment-icon {
            width: 40px;
            height: 26px;
            font-size: 0.6rem;
        }
    }

    /* Features Section */
    .features-section {
        padding: 100px 0;
        background: white;
    }

    .section-label {
        display: inline-block;
        background: linear-gradient(135deg, #eff6ff 0%, #faf5ff 100%);
        color: var(--accent);
        padding: 10px 24px;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 20px;
    }

    .section-title {
        font-size: clamp(2rem, 4vw, 2.75rem);
        font-weight: 800;
        color: var(--primary-dark);
        margin-bottom: 16px;
        letter-spacing: -0.02em;
    }

    .section-subtitle {
        font-size: 1.125rem;
        color: var(--text-muted);
        max-width: 600px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 28px;
        margin-top: 60px;
    }

    .feature-card {
        background: white;
        border-radius: 20px;
        padding: 36px;
        border: 1px solid #e2e8f0;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
        border-color: transparent;
    }

    .feature-card:hover::before {
        opacity: 1;
    }

    .feature-card:nth-child(2)::before {
        background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%);
    }

    .feature-card:nth-child(3)::before {
        background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
    }

    .feature-card:nth-child(4)::before {
        background: linear-gradient(90deg, #fa709a 0%, #fee140 100%);
    }

    .feature-card:nth-child(5)::before {
        background: linear-gradient(90deg, #a8edea 0%, #fed6e3 100%);
    }

    .feature-card:nth-child(6)::before {
        background: linear-gradient(90deg, #d299c2 0%, #fef9d7 100%);
    }

    .feature-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 24px;
    }

    .feature-icon.purple {
        background: #f3e8ff;
        color: #9333ea;
    }

    .feature-icon.blue {
        background: #dbeafe;
        color: #2563eb;
    }

    .feature-icon.green {
        background: #dcfce7;
        color: #16a34a;
    }

    .feature-icon.orange {
        background: #ffedd5;
        color: #ea580c;
    }

    .feature-icon.pink {
        background: #fce7f3;
        color: #db2777;
    }

    .feature-icon.cyan {
        background: #cffafe;
        color: #0891b2;
    }

    .feature-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 12px;
    }

    .feature-text {
        color: var(--text-muted);
        line-height: 1.7;
        margin: 0;
    }

    /* How It Works */
    .how-section {
        padding: 100px 0;
        background: var(--bg-light);
    }

    .steps-container {
        margin-top: 50px;
    }

    .step-item {
        display: flex;
        gap: 28px;
        margin-bottom: 40px;
        position: relative;
    }

    .step-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 32px;
        top: 72px;
        bottom: -40px;
        width: 2px;
        background: linear-gradient(180deg, var(--accent) 0%, #e2e8f0 100%);
    }

    .step-number {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        background: var(--accent);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        flex-shrink: 0;
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
    }

    .step-content h3 {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 10px;
    }

    .step-content p {
        color: var(--text-muted);
        line-height: 1.7;
        margin: 0;
        max-width: 400px;
    }

    /* Pricing Section */
    .pricing-section {
        padding: 100px 0;
        background: white;
    }

    .pricing-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 28px;
        margin-top: 60px;
    }

    .price-card {
        background: white;
        border-radius: 24px;
        padding: 40px;
        border: 2px solid #e2e8f0;
        position: relative;
        transition: all 0.4s ease;
    }

    .price-card.featured {
        border-color: var(--accent);
        box-shadow: 0 25px 50px rgba(59, 130, 246, 0.15);
        transform: scale(1.02);
    }

    .price-card.featured::before {
        content: 'Most Popular';
        position: absolute;
        top: -14px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--accent);
        color: white;
        padding: 6px 20px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .price-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
    }

    .price-card.featured:hover {
        transform: scale(1.02) translateY(-8px);
    }

    .price-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 8px;
    }

    .price-desc {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin-bottom: 24px;
    }

    .price-amount {
        display: flex;
        align-items: baseline;
        gap: 4px;
        margin-bottom: 28px;
    }

    .price-currency {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-dark);
    }

    .price-value {
        font-size: 3.5rem;
        font-weight: 800;
        color: var(--primary-dark);
        line-height: 1;
    }

    .price-period {
        color: var(--text-muted);
        font-size: 1rem;
    }

    .price-features {
        list-style: none;
        padding: 0;
        margin: 0 0 32px;
    }

    .price-features li {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 0;
        border-bottom: 1px solid #f1f5f9;
        color: var(--text-primary);
        font-size: 0.95rem;
    }

    .price-features li:last-child {
        border-bottom: none;
    }

    .price-features li i {
        color: var(--success);
        font-size: 1.2rem;
    }

    .price-features li.disabled {
        color: #cbd5e1;
    }

    .price-features li.disabled i {
        color: #cbd5e1;
    }

    .btn-price {
        width: 100%;
        padding: 16px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    .btn-price.primary {
        background: var(--accent);
        color: white;
        border: none;
    }

    .btn-price.primary:hover {
        background: var(--accent-hover);
        transform: translateY(-2px);
        color: white;
    }

    .btn-price.outline {
        background: transparent;
        color: var(--primary-dark);
        border: 2px solid #e2e8f0;
    }

    .btn-price.outline:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    /* Business Types Section */
    .business-section {
        padding: 100px 0;
        background: var(--bg-light);
    }

    .business-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-top: 60px;
    }

    .business-card {
        background: white;
        border-radius: 20px;
        padding: 36px 28px;
        text-align: center;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .business-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        border-color: var(--accent);
    }

    .business-icon {
        width: 70px;
        height: 70px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #eff6ff 0%, #faf5ff 100%);
        color: var(--accent);
    }

    .business-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 8px;
    }

    .business-text {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin: 0;
    }

    /* CTA Section */
    .cta-section {
        padding: 100px 0;
        background: var(--primary-dark);
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background:
            radial-gradient(ellipse 60% 40% at 20% 80%, rgba(59, 130, 246, 0.3), transparent),
            radial-gradient(ellipse 60% 40% at 80% 20%, rgba(139, 92, 246, 0.2), transparent);
    }

    .cta-content {
        position: relative;
        z-index: 10;
        text-align: center;
        max-width: 700px;
        margin: 0 auto;
    }

    .cta-title {
        font-size: clamp(2rem, 4vw, 2.75rem);
        font-weight: 800;
        color: white;
        margin-bottom: 20px;
    }

    .cta-text {
        font-size: 1.2rem;
        color: #94a3b8;
        margin-bottom: 40px;
        line-height: 1.7;
    }

    .cta-buttons {
        display: flex;
        justify-content: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    /* Featured Stores */
    .stores-section {
        padding: 80px 0;
        background: white;
    }

    /* Footer */
    .footer-modern {
        background: #0a0f1c;
        padding: 80px 0 40px;
        color: #94a3b8;
    }

    .footer-brand h4 {
        color: white;
        font-weight: 700;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }

    .footer-brand p {
        color: #64748b;
        max-width: 300px;
        line-height: 1.7;
    }

    .footer-title {
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 24px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 14px;
    }

    .footer-links a {
        color: #64748b;
        text-decoration: none;
        transition: color 0.3s ease;
        font-size: 0.95rem;
    }

    .footer-links a:hover {
        color: white;
    }

    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 40px;
        margin-top: 60px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .footer-social {
        display: flex;
        gap: 12px;
    }

    .footer-social a {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        transition: all 0.3s ease;
        font-size: 1.1rem;
    }

    .footer-social a:hover {
        background: var(--accent);
        color: white;
        transform: translateY(-3px);
    }

    /* Responsive */
    @media (max-width: 991px) {
        .hero-visual {
            margin-top: 60px;
        }

        .floating-card {
            display: none;
        }

        .features-grid {
            grid-template-columns: 1fr;
        }

        .price-card.featured {
            transform: none;
        }

        .price-card.featured:hover {
            transform: translateY(-8px);
        }
    }

    @media (max-width: 767px) {
        .hero-modern {
            padding-top: 60px;
        }

        .hero-buttons {
            flex-direction: column;
            align-items: flex-start;
        }

        .hero-trust {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .step-item {
            flex-direction: column;
            gap: 16px;
        }

        .step-item::after {
            display: none;
        }

        .pricing-cards {
            grid-template-columns: 1fr;
        }

        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }

        .payment-strip {
            flex-direction: column;
            gap: 20px;
        }
    }
    
    /* Mobile view improvements */
    @media (max-width: 575px) {
        .hero-modern {
            min-height: auto;
            padding-top: 60px;
            padding-bottom: 40px;
        }
        
        .hero-title {
            font-size: 1.75rem;
        }
        
        .hero-description {
            font-size: 1rem;
        }
        
        .features-section,
        .how-it-works-section,
        .pricing-section,
        .testimonials-section,
        .cta-section {
            padding: 50px 0;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .dashboard-preview {
            padding: 12px;
        }
        
        .floating-card {
            display: none;
        }
        
        .btn-glow, .btn-ghost {
            padding: 12px 24px;
            font-size: 0.9rem;
            width: 100%;
        }
        
        .hero-buttons {
            flex-direction: column;
            width: 100%;
        }
        
        .hero-trust {
            flex-direction: column;
            gap: 12px;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-modern">
    <div class="hero-grid"></div>
    <div class="container hero-content py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 py-5">
                <div class="hero-badge">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <span>Next-Gen Point of Sale System</span>
                </div>

                <h1 class="hero-title">
                    Run Your Business<br>
                    <span class="highlight">Smarter & Faster</span>
                </h1>

                <p class="hero-description">
                    A complete POS solution with QR ordering, GST management, inventory tracking,
                    and real-time analytics. Built for Indian businesses.
                </p>

                <div class="hero-buttons">
                    @guest
                    <a href="{{ route('register') }}" class="btn-glow">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                        Start Free Trial
                    </a>
                    <a href="{{ route('pricing') }}" class="btn-ghost">
                        <i class="bi bi-grid-3x3-gap"></i>
                        See Plans
                    </a>
                    @else
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-glow">
                        <i class="bi bi-speedometer2"></i>
                        Admin Dashboard
                    </a>
                    @elseif(auth()->user()->isStoreOwner() || auth()->user()->isStaff())
                    <a href="{{ route('store-owner.pos.index') }}" class="btn-glow">
                        <i class="bi bi-display"></i>
                        Open POS
                    </a>
                    <a href="{{ route('store-owner.dashboard') }}" class="btn-ghost">
                        <i class="bi bi-shop"></i>
                        Dashboard
                    </a>
                    @else
                    <a href="{{ route('orders.index') }}" class="btn-glow">
                        <i class="bi bi-bag-check"></i>
                        My Orders
                    </a>
                    @endif
                    @endguest
                </div>

                <div class="hero-trust">
                    <div class="trust-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Free 14-day trial</span>
                    </div>
                    <div class="trust-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>No credit card required</span>
                    </div>
                    <div class="trust-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Cancel anytime</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-visual">
                    <div class="dashboard-preview">
                        <div class="dashboard-header">
                            <div class="dashboard-dot red"></div>
                            <div class="dashboard-dot yellow"></div>
                            <div class="dashboard-dot green"></div>
                            <span class="dashboard-title">POS Dashboard</span>
                        </div>
                        <div class="dashboard-content">
                            <div class="dashboard-stats">
                                <div class="stat-card">
                                    <div class="stat-label">Today's Sales</div>
                                    <div class="stat-value green">₹12,450</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-label">Orders</div>
                                    <div class="stat-value blue">24</div>
                                </div>
                            </div>
                            <div class="chart-visual">
                                <div class="chart-label">Weekly Performance</div>
                                <div class="chart-bars">
                                    <div class="chart-bar"></div>
                                    <div class="chart-bar"></div>
                                    <div class="chart-bar"></div>
                                    <div class="chart-bar"></div>
                                    <div class="chart-bar"></div>
                                    <div class="chart-bar"></div>
                                    <div class="chart-bar"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="floating-card card-1">
                        <div class="d-flex align-items-center">
                            <div class="floating-icon green">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="floating-content">
                                <div class="floating-title">Payment Received</div>
                                <div class="floating-subtitle">₹850.00 via UPI</div>
                            </div>
                        </div>
                    </div>

                    <div class="floating-card card-2">
                        <div class="d-flex align-items-center">
                            <div class="floating-icon blue">
                                <i class="bi bi-qr-code"></i>
                            </div>
                            <div class="floating-content">
                                <div class="floating-title">New QR Order</div>
                                <div class="floating-subtitle">Table 5 • 3 items</div>
                            </div>
                        </div>
                    </div>

                    <div class="floating-card card-3">
                        <div class="d-flex align-items-center">
                            <div class="floating-icon orange">
                                <i class="bi bi-bell"></i>
                            </div>
                            <div class="floating-content">
                                <div class="floating-title">Low Stock Alert</div>
                                <div class="floating-subtitle">5 items running low</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="payment-strip">
            <span class="payment-label">Integrated Payments:</span>
            <div class="payment-icons">
                <div class="payment-icon">UPI</div>
                <div class="payment-icon">VISA</div>
                <div class="payment-icon">MC</div>
                <div class="payment-icon">Razorpay</div>
                <div class="payment-icon">Stripe</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label">Features</div>
            <h2 class="section-title">Everything You Need to Succeed</h2>
            <p class="section-subtitle mx-auto">
                Powerful tools designed specifically for Indian retailers, restaurants, and service businesses.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon purple">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <h3 class="feature-title">QR Code Ordering</h3>
                <p class="feature-text">
                    Customers scan your unique QR code to browse menu, place orders, and pay directly.
                    Reduce staff workload and eliminate order errors.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon green">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <h3 class="feature-title">GST Compliant Billing</h3>
                <p class="feature-text">
                    Automatic CGST, SGST, IGST calculations. Generate GST-compliant invoices and
                    comprehensive tax reports for easy filing.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon blue">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <h3 class="feature-title">Cash Register Management</h3>
                <p class="feature-text">
                    Track opening cash, monitor transactions in real-time, and reconcile at day end.
                    Complete visibility into your cash flow.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon orange">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h3 class="feature-title">Inventory Tracking</h3>
                <p class="feature-text">
                    Real-time stock levels, low stock alerts, and automatic updates when orders are placed.
                    Never run out of your bestsellers.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon pink">
                    <i class="bi bi-people"></i>
                </div>
                <h3 class="feature-title">Staff & Permissions</h3>
                <p class="feature-text">
                    Create staff accounts with role-based access. Cashiers, managers, and admins
                    each see only what they need.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon cyan">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <h3 class="feature-title">Analytics & Insights</h3>
                <p class="feature-text">
                    Daily, weekly, monthly sales reports. Track best-selling items, peak hours,
                    and customer trends to make smarter decisions.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="how-section" id="how-it-works">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5">
                <div class="section-label">How It Works</div>
                <h2 class="section-title">Get Started in 5 Minutes</h2>
                <p class="section-subtitle">
                    No technical expertise needed. Set up your store and start accepting orders today.
                </p>
            </div>

            <div class="col-lg-7">
                <div class="steps-container">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3>Create Your Store</h3>
                            <p>Sign up, enter your business details, and add your products with prices. It takes just a few minutes.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3>Configure Settings</h3>
                            <p>Set up your GST rates, payment methods, staff accounts, and customize receipts to match your brand.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3>Start Selling</h3>
                            <p>Print your store QR code, train your staff on the POS, and you're ready to serve customers!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Business Types -->
<section class="business-section">
    <div class="container">
        <div class="text-center">
            <div class="section-label">Who It's For</div>
            <h2 class="section-title">Built for Every Business</h2>
            <p class="section-subtitle mx-auto">
                Whether you run a restaurant, retail store, or service business — we've got you covered.
            </p>
        </div>

        <div class="business-grid">
            <div class="business-card">
                <div class="business-icon">
                    <i class="bi bi-cup-hot"></i>
                </div>
                <h3 class="business-title">Restaurants & Cafes</h3>
                <p class="business-text">Table ordering, kitchen display, and dine-in management</p>
            </div>

            <div class="business-card">
                <div class="business-icon">
                    <i class="bi bi-shop-window"></i>
                </div>
                <h3 class="business-title">Retail Stores</h3>
                <p class="business-text">Inventory tracking, barcode scanning, and quick billing</p>
            </div>

            <div class="business-card">
                <div class="business-icon">
                    <i class="bi bi-cart4"></i>
                </div>
                <h3 class="business-title">Grocery & Kirana</h3>
                <p class="business-text">Weight-based pricing, credit accounts, and stock alerts</p>
            </div>

            <div class="business-card">
                <div class="business-icon">
                    <i class="bi bi-scissors"></i>
                </div>
                <h3 class="business-title">Salons & Spas</h3>
                <p class="business-text">Service booking, staff schedules, and package billing</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Preview -->
<section class="pricing-section" id="pricing">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label">Pricing</div>
            <h2 class="section-title">Simple, Transparent Pricing</h2>
            <p class="section-subtitle mx-auto">
                Choose the plan that fits your business. Upgrade or downgrade anytime.
            </p>
        </div>

        <div class="pricing-cards">
            @php
            $plans = \App\Models\Plan::where('is_active', true)->orderBy('price')->take(3)->get();
            @endphp

            @forelse($plans as $index => $plan)
            <div class="price-card {{ $index === 1 ? 'featured' : '' }}">
                <div class="price-name">{{ $plan->name }}</div>
                <div class="price-desc">{{ $plan->description ?? 'Perfect for your business' }}</div>
                <div class="price-amount">
                    <span class="price-currency">₹</span>
                    <span class="price-value">{{ number_format($plan->price, 0) }}</span>
                    <span class="price-period">/{{ $plan->billing_cycle }}</span>
                </div>
                <ul class="price-features">
                    @if($plan->features && count($plan->features) > 0)
                    @php
                    $features = is_array($plan->features) ? $plan->features : json_decode($plan->features, true) ?? [];
                    @endphp
                    @foreach($features as $feature => $enabled)
                    @if(!is_numeric($feature))
                    <li class="{{ !$enabled ? 'disabled' : '' }}">
                        <i class="bi bi-{{ $enabled ? 'check-circle-fill' : 'x-circle' }}"></i>
                        {{ ucwords(str_replace('_', ' ', $feature)) }}
                    </li>
                    @endif
                    @endforeach
                    @else
                    <li><i class="bi bi-check-circle-fill"></i> All basic features</li>
                    <li><i class="bi bi-check-circle-fill"></i> QR code ordering</li>
                    <li><i class="bi bi-check-circle-fill"></i> GST billing</li>
                    @endif
                </ul>
                <a href="{{ route('pricing') }}" class="btn-price {{ $index === 1 ? 'primary' : 'outline' }}">Get Started</a>
            </div>
            @empty
            <!-- Default pricing if no plans exist -->
            <div class="price-card">
                <div class="price-name">Starter</div>
                <div class="price-desc">For small businesses getting started</div>
                <div class="price-amount">
                    <span class="price-currency">₹</span>
                    <span class="price-value">499</span>
                    <span class="price-period">/month</span>
                </div>
                <ul class="price-features">
                    <li><i class="bi bi-check-circle-fill"></i> Up to 100 products</li>
                    <li><i class="bi bi-check-circle-fill"></i> QR code ordering</li>
                    <li><i class="bi bi-check-circle-fill"></i> Basic reporting</li>
                    <li><i class="bi bi-check-circle-fill"></i> 1 staff account</li>
                    <li class="disabled"><i class="bi bi-x-circle"></i> Inventory management</li>
                    <li class="disabled"><i class="bi bi-x-circle"></i> Priority support</li>
                </ul>
                <a href="{{ route('pricing') }}" class="btn-price outline">Get Started</a>
            </div>

            <div class="price-card featured">
                <div class="price-name">Professional</div>
                <div class="price-desc">For growing businesses</div>
                <div class="price-amount">
                    <span class="price-currency">₹</span>
                    <span class="price-value">999</span>
                    <span class="price-period">/month</span>
                </div>
                <ul class="price-features">
                    <li><i class="bi bi-check-circle-fill"></i> Unlimited products</li>
                    <li><i class="bi bi-check-circle-fill"></i> QR code ordering</li>
                    <li><i class="bi bi-check-circle-fill"></i> Advanced analytics</li>
                    <li><i class="bi bi-check-circle-fill"></i> 5 staff accounts</li>
                    <li><i class="bi bi-check-circle-fill"></i> Inventory management</li>
                    <li><i class="bi bi-check-circle-fill"></i> GST reports</li>
                </ul>
                <a href="{{ route('pricing') }}" class="btn-price primary">Get Started</a>
            </div>

            <div class="price-card">
                <div class="price-name">Enterprise</div>
                <div class="price-desc">For large operations</div>
                <div class="price-amount">
                    <span class="price-currency">₹</span>
                    <span class="price-value">2499</span>
                    <span class="price-period">/month</span>
                </div>
                <ul class="price-features">
                    <li><i class="bi bi-check-circle-fill"></i> Everything in Pro</li>
                    <li><i class="bi bi-check-circle-fill"></i> Multiple locations</li>
                    <li><i class="bi bi-check-circle-fill"></i> API access</li>
                    <li><i class="bi bi-check-circle-fill"></i> Unlimited staff</li>
                    <li><i class="bi bi-check-circle-fill"></i> Priority support</li>
                    <li><i class="bi bi-check-circle-fill"></i> Custom integrations</li>
                </ul>
                <a href="{{ route('pricing') }}" class="btn-price outline">Contact Sales</a>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Featured Stores -->
@if($featuredStores->count() > 0)
<section class="stores-section" id="stores">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label">Our Stores</div>
            <h2 class="section-title">Businesses Using Our Platform</h2>
            <p class="section-subtitle mx-auto">
                Join these businesses who trust our POS system for their daily operations.
            </p>
        </div>
        <div class="row g-4">
            @foreach($featuredStores as $store)
            <div class="col-md-3">
                <div class="business-card">
                    @if($store->logo)
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}"
                        class="rounded-circle mb-3" style="width: 70px; height: 70px; object-fit: cover;">
                    @else
                    <div class="business-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); color: white;">
                        {{ strtoupper(substr($store->name, 0, 1)) }}
                    </div>
                    @endif
                    <h3 class="business-title">{{ $store->name }}</h3>
                    <p class="business-text">
                        <span class="badge bg-light text-dark">{{ ucfirst($store->type) }}</span>
                    </p>
                    <a href="{{ route('store.show', $store->slug) }}" class="btn btn-sm btn-outline-primary mt-2">
                        Visit Store
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Ready to Modernize Your Business?</h2>
            <p class="cta-text">
                Start your free 14-day trial today. No credit card required.
                Set up takes just 5 minutes.
            </p>
            <div class="cta-buttons">
                <a href="{{ route('register') }}" class="btn-glow">
                    <i class="bi bi-rocket-takeoff-fill"></i>
                    Start Free Trial
                </a>
                <a href="{{ route('pricing') }}" class="btn-ghost">
                    <i class="bi bi-telephone"></i>
                    Talk to Sales
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer-modern">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand">
                    <h4><i class="bi bi-shop"></i> POS System</h4>
                    <p>A modern point of sale solution built for Indian businesses. Simplify operations, grow sales, and delight customers.</p>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="footer-title">Product</h5>
                <ul class="footer-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="{{ route('pricing') }}">Pricing</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="footer-title">Company</h5>
                <ul class="footer-links">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Blog</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="footer-title">Legal</h5>
                <ul class="footer-links">
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Refund Policy</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="footer-title">Support</h5>
                <ul class="footer-links">
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Documentation</a></li>
                    <li><a href="#">API Reference</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div>© {{ date('Y') }} POS System. All rights reserved.</div>
            <div class="footer-social">
                <a href="#"><i class="bi bi-twitter-x"></i></a>
                <a href="#"><i class="bi bi-linkedin"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-youtube"></i></a>
            </div>
        </div>
    </div>
</footer>
@endsection