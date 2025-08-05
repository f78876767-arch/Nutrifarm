<?php

namespace App\Providers\Filament;

use Filament\PanelProvider;
use Filament\Panel;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('test')
            ->path('test-admin')
            ->login();
    }
}
