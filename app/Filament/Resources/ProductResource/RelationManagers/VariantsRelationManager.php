<?php
namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('value')->required(),
            Forms\Components\TextInput::make('price')->numeric()->nullable(),
            Forms\Components\TextInput::make('stock')->numeric()->nullable(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('value'),
            Tables\Columns\TextColumn::make('price')->money('IDR', true),
            Tables\Columns\TextColumn::make('stock'),
        ]);
    }
}
