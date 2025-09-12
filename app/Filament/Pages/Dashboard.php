<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        // Only show custom order stats widget, remove default Filament cards
        return [
            \App\Filament\Widgets\OrderStatsWidget::class,
        ];
    }
}
