<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    // Admin does not need to create new orders manually, so no header actions (no New Order button)
    protected function getHeaderActions(): array
    {
        return [];
    }
}
