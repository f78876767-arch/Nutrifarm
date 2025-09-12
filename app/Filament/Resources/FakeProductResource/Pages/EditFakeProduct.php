<?php

namespace App\Filament\Resources\FakeProductResource\Pages;

use App\Filament\Resources\FakeProductResource;
use Filament\Resources\Pages\EditRecord;

class EditFakeProduct extends EditRecord
{
    protected static string $resource = FakeProductResource::class;
}
