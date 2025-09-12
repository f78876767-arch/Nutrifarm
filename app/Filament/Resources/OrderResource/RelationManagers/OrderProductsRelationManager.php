<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class OrderProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderProducts';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')->label('Product'),
                TextColumn::make('quantity'),
                TextColumn::make('price')->money('IDR', true),
            ]);
    }
}
