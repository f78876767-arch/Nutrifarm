<?php

namespace App\Filament\Resources\SimpleProductResource\Pages;

use App\Filament\Resources\SimpleProductResource;
use Filament\Resources\Pages\ListRecords;

class ListSimpleProducts extends ListRecords
{
    protected static string $resource = SimpleProductResource::class;
}
