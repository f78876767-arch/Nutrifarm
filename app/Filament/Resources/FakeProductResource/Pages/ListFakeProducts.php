<?php

namespace App\Filament\Resources\FakeProductResource\Pages;

use App\Filament\Resources\FakeProductResource;
use Filament\Resources\Pages\ListRecords;

class ListFakeProducts extends ListRecords
{
    protected static string $resource = FakeProductResource::class;
}
