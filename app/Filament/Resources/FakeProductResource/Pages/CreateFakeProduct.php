<?php

namespace App\Filament\Resources\FakeProductResource\Pages;

use App\Filament\Resources\FakeProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFakeProduct extends CreateRecord
{
    protected static string $resource = FakeProductResource::class;
}
