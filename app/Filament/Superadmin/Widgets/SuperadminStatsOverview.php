<?php

namespace App\Filament\Superadmin\Widgets;

use App\Business;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Superadmin\Entities\Subscription;

class SuperadminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->toDateString();

        $newSubscriptionsToday = Subscription::whereDate('created_at', $today)->count();
        $newRegistrationsToday = Business::whereDate('created_at', $today)->count();
        $totalBusinesses       = Business::count();

        $notSubscribed = Business::whereDoesntHave('subscriptions', function ($q) {
            $q->where('status', 'approved');
        })->count();

        return [
            Stat::make('New Subscriptions Today', $newSubscriptionsToday)
                ->description('Active subscriptions created today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('New Registrations Today', $newRegistrationsToday)
                ->description('Businesses registered today')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('Total Businesses', $totalBusinesses)
                ->description("{$notSubscribed} without active subscription")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($notSubscribed > 0 ? 'warning' : 'success'),
        ];
    }
}
