<?php

namespace App\Filament\Superadmin\Pages;

use App\Filament\Superadmin\Widgets\SuperadminStatsOverview;
use Filament\Pages\Dashboard;

class SuperadminDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    protected static ?int $navigationSort = 0;

    public function getWidgets(): array
    {
        return [
            SuperadminStatsOverview::class,
        ];
    }
}
