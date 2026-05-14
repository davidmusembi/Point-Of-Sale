<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AfiPOS') }} — Smart Point of Sale</title>
    <meta name="description" content="Multi-tenant cloud POS and inventory management for modern businesses.">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #1e293b;
            background: #fff;
            line-height: 1.6;
        }

        a { text-decoration: none; color: inherit; }

        /* Nav */
        nav {
            position: sticky; top: 0; z-index: 50;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2rem; height: 64px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }

        .nav-brand {
            display: flex; align-items: center; gap: 0.6rem;
            font-size: 1.2rem; font-weight: 700; color: #4f46e5;
        }

        .nav-brand img { width: 32px; height: 32px; border-radius: 50%; }

        .nav-links { display: flex; align-items: center; gap: 1rem; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.5rem 1.25rem; border-radius: 8px;
            font-size: 0.875rem; font-weight: 600; cursor: pointer;
            transition: opacity .15s, transform .1s;
        }
        .btn:hover { opacity: .88; transform: translateY(-1px); }

        .btn-outline {
            border: 1.5px solid #4f46e5; color: #4f46e5;
        }

        .btn-primary {
            background: #4f46e5; color: #fff; border: none;
        }

        .btn-lg {
            padding: 0.75rem 2rem; font-size: 1rem; border-radius: 10px;
        }

        /* Hero */
        .hero {
            text-align: center;
            padding: 6rem 1.5rem 4rem;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 60%, #f0fdf4 100%);
        }

        .hero-badge {
            display: inline-block;
            background: #e0e7ff; color: #4338ca;
            font-size: 0.75rem; font-weight: 700; letter-spacing: .05em;
            text-transform: uppercase; border-radius: 999px;
            padding: 0.25rem 0.9rem; margin-bottom: 1.5rem;
        }

        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800; line-height: 1.15;
            color: #1e293b;
            margin-bottom: 1.25rem;
        }

        .hero h1 span { color: #4f46e5; }

        .hero p {
            font-size: 1.125rem; color: #475569;
            max-width: 560px; margin: 0 auto 2.5rem;
        }

        .hero-cta { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }

        /* Features */
        .features {
            padding: 5rem 1.5rem;
            max-width: 1100px; margin: 0 auto;
        }

        .section-label {
            text-align: center;
            font-size: 0.8rem; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: #4f46e5;
            margin-bottom: 0.75rem;
        }

        .section-title {
            text-align: center;
            font-size: clamp(1.5rem, 3vw, 2.25rem);
            font-weight: 800; color: #1e293b;
            margin-bottom: 3rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
        }

        .feature-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.75rem;
            transition: box-shadow .2s, transform .2s;
        }

        .feature-card:hover {
            box-shadow: 0 8px 30px rgba(79,70,229,.1);
            transform: translateY(-3px);
        }

        .feature-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: #e0e7ff;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1rem; font-weight: 700;
            margin-bottom: 0.4rem; color: #1e293b;
        }

        .feature-card p { font-size: 0.875rem; color: #64748b; }

        /* CTA band */
        .cta-band {
            background: #4f46e5;
            color: #fff;
            text-align: center;
            padding: 4rem 1.5rem;
        }

        .cta-band h2 {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 800; margin-bottom: 0.75rem;
        }

        .cta-band p {
            font-size: 1rem; opacity: .85;
            margin-bottom: 2rem; max-width: 480px; margin-left: auto; margin-right: auto;
        }

        .btn-white {
            background: #fff; color: #4f46e5; border: none;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 1.75rem 1.5rem;
            font-size: 0.8rem; color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }

        @media (max-width: 480px) {
            nav { padding: 0 1rem; }
            .nav-links .btn-outline { display: none; }
        }
    </style>
</head>
<body>

{{-- Nav --}}
<nav>
    <a class="nav-brand" href="/">
        <img src="{{ asset('img/logo-small.png') }}" alt="{{ config('app.name') }} logo">
        {{ config('app.name', 'AfiPOS') }}
    </a>
    <div class="nav-links">
        <a class="btn btn-outline" href="{{ url('/login') }}">Sign In</a>
        <a class="btn btn-primary" href="{{ url('/sadmin') }}">Admin Panel</a>
    </div>
</nav>

{{-- Hero --}}
<section class="hero">
    <div class="hero-badge">Cloud POS Platform</div>
    <h1>Run your business<br>with <span>{{ config('app.name', 'AfiPOS') }}</span></h1>
    <p>Multi-tenant point of sale, inventory, and accounting — one platform, every business.</p>
    <div class="hero-cta">
        <a class="btn btn-primary btn-lg" href="{{ url('/sadmin') }}">Go to Admin Panel</a>
        <a class="btn btn-outline btn-lg" href="{{ url('/login') }}">Tenant Login</a>
    </div>
</section>

{{-- Features --}}
<section class="features">
    <div class="section-label">What's inside</div>
    <div class="section-title">Everything you need to sell smarter</div>
    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">🏪</div>
            <h3>Multi-Location POS</h3>
            <p>Manage sales across multiple locations from a single dashboard with real-time sync.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📦</div>
            <h3>Inventory Management</h3>
            <p>Track stock levels, set reorder points, and manage products with FIFO, LIFO, or AVCO costing.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Accounting & Reports</h3>
            <p>Double-entry accounting, profit & loss, balance sheet, and detailed sales analytics built in.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">👥</div>
            <h3>CRM & Contacts</h3>
            <p>Manage customers and suppliers, track credit limits, and run loyalty programs with ease.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🏢</div>
            <h3>Multi-Tenant SaaS</h3>
            <p>Each business gets its own isolated database and subdomain — fully managed from one panel.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💳</div>
            <h3>Multiple Payments</h3>
            <p>Accept cash, card, M-Pesa, Stripe, PayPal, Paystack, Flutterwave, and more at checkout.</p>
        </div>
    </div>
</section>

{{-- CTA band --}}
<section class="cta-band">
    <h2>Ready to get started?</h2>
    <p>Sign in to the admin panel to provision a new business or manage your existing tenants.</p>
    <a class="btn btn-white btn-lg" href="{{ url('/sadmin') }}">Open Admin Panel →</a>
</section>

{{-- Footer --}}
<footer>
    &copy; {{ date('Y') }} {{ config('app.name', 'AfiPOS') }} &mdash; Powered by
    <a href="https://nairobyte.xyz" style="color:#4f46e5;">Nairobyte</a>
</footer>

</body>
</html>
