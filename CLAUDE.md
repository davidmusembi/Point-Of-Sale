# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Install dependencies
composer install
npm install

# Environment setup (first time)
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Development
php artisan serve          # Start dev server
npm run dev                # Compile frontend assets

# Testing
php artisan test                              # Run all tests
php artisan test --filter=ClassName           # Run a specific test class
php artisan test --filter=methodName          # Run a specific test method

# Database
php artisan migrate                           # Run pending migrations
php artisan migrate:rollback                  # Rollback last batch
php artisan db:seed                           # Run seeders

# Generate API docs
php artisan scribe:generate
```

## Architecture

**ultimatePOS** is a multi-tenant, modular Laravel 9/10 POS and inventory management system.

### Multi-Tenancy

Uses `stancl/tenancy` (v3.8) for database-per-tenant isolation. Each business (tenant) gets its own isolated database. Tenant-specific routes are defined in `routes/tenant_app.php` (300+ routes). The `AppServiceProvider` and `TenancyServiceProvider` in `app/Providers/` handle bootstrapping.

### Modules System

Feature modules live in `Modules/` using `nwidart/laravel-modules`. Active modules:
- `Superadmin/` — SaaS management, package/resource limits for tenants
- `Essentials/` — HRM (attendance, leaves, allowances), documents, messages, todos
- `Manufacturing/` — Production recipes and manufacturing orders

Each module has its own `Routes/`, `Http/Controllers/`, `Entities/` (models), `Resources/views/`, and `Database/` subdirectories.

### Business Logic Layer

Controllers are kept lean. All complex business logic lives in `app/Utils/`:
- `TransactionUtil` — Core sales/purchase transaction processing
- `ProductUtil` — Product operations, stock calculations, FIFO/LIFO/AVCO costing
- `BusinessUtil` — Business configuration and settings
- `ContactUtil` — Customer and supplier operations
- `CashRegisterUtil` — Cash register open/close/reconciliation
- `TaxUtil` — Tax rate calculations and group taxes
- `AccountTransactionUtil` — Double-entry accounting operations
- `ModuleUtil` — Module enable/disable and feature flags
- `RestaurantUtil` — Restaurant-specific table and service logic

### Routing Structure

| File | Purpose |
|------|---------|
| `routes/web.php` | Public registration and welcome routes |
| `routes/api.php` | REST API v1 (`/api/v1/*`) with Passport auth |
| `routes/tenant_app.php` | All authenticated tenant (business) routes |
| `Modules/*/Routes/web.php` | Module-specific web routes |
| `Modules/*/Routes/api.php` | Module-specific API routes |

### API

The V1 API uses Laravel Passport (OAuth2). Public endpoint: `POST /api/v1/auth/login`. All other routes require the `api.v1.auth` middleware. API controllers live in `app/Http/Controllers/Api/V1/` and extend `App\Http\Controllers\Api\V1\BaseController`.

### Frontend

Blade templates with AdminLTE admin theme. Heavy use of jQuery DataTables for data-heavy views. Vue.js components for interactive widgets. Assets compiled via Laravel Mix (webpack). Tailwind CSS is referenced in README but AdminLTE/Bootstrap are the primary UI framework in practice.

### RBAC

`spatie/laravel-permission` (v5.5). Roles and permissions are seeded via `PermissionsSeeder`. Check permissions using `$user->can('permission_name')` or `@can` in Blade.

### Key Models Location

Models follow legacy Laravel style — they live directly in `app/` root (not `app/Models/`). Module-specific entities live in `Modules/{Name}/Entities/`.

### Database Patterns

- Accounting methods per business: `FIFO`, `LIFO`, `AVCO`
- All financial amounts stored as decimals
- `business_id` scoping on most tenant tables
- `location_id` for multi-location businesses
- Soft deletes used on transactional records
- `packages` table controls SaaS resource limits (`business_count`, `resource_limits` JSON)

### Payment Gateways

Configured via `.env`: Stripe, PayPal (v1 + v2), Razorpay, PesaPal, Paystack, Flutterwave, MyFatoorah. Payment processing flows through `TransactionPaymentController` and `TransactionUtil`.

### Key Config Files

- `config/constants.php` — POS-specific enums and constants
- `config/menus.php` — Admin sidebar menu definitions
- `config/tenancy.php` — Tenant isolation settings
- `config/modules.php` — Module system settings
- `app/Http/helpers.php` — Global helper functions available everywhere
