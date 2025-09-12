<?php
namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('Order ID'),
            Tables\Columns\TextColumn::make('created_at')->dateTime('Y-m-d H:i'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('total')->money('IDR', true),
        ]);
    }
}
