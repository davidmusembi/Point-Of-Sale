<?php

namespace App\Providers\Filament;

use App\Filament\Superadmin\Pages\SuperadminDashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('superadmin')
            ->path('sadmin')
            ->brandName(config('app.name', 'ultimatePOS') . ' · SuperAdmin')
            ->colors(['primary' => Color::Indigo])
            ->discoverResources(
                in: app_path('Filament/Superadmin/Resources'),
                for: 'App\\Filament\\Superadmin\\Resources'
            )
            ->pages([SuperadminDashboard::class])
            ->discoverWidgets(
                in: app_path('Filament/Superadmin/Widgets'),
                for: 'App\\Filament\\Superadmin\\Widgets'
            )
            ->widgets([])
            ->navigationGroups([
                NavigationGroup::make('SaaS Management'),
                NavigationGroup::make('Billing'),
                NavigationGroup::make('System'),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\PreventAccessFromTenantDomains::class,
            ])
            ->authMiddleware([Authenticate::class])
            ->authGuard('web')
            ->login();
    }
}
