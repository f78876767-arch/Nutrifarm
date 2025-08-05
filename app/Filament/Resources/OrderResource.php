<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('resi')->label('Resi (Tracking Number)'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'shipped' => 'Shipped',
                        'cancelled' => 'Cancelled',
                    ])->disabled(fn($record) => $record?->status === 'cancelled'),
                Forms\Components\Select::make('cancel_reason')
                    ->label('Alasan Pembatalan')
                    ->options([
                        'stok_habis' => 'Stok habis',
                        'permintaan_pelanggan' => 'Permintaan pelanggan',
                        'pembayaran_gagal' => 'Pembayaran gagal',
                        'lainnya' => 'Lainnya',
                    ])
                    ->required(fn($get) => $get('status') === 'cancelled')
                    ->visible(fn($get) => $get('status') === 'cancelled'),
                Forms\Components\TextInput::make('shipping_method')->disabled(),
                Forms\Components\TextInput::make('payment_status')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')->label('Invoice No')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('User')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('total')->money('IDR', true)->sortable(),
                Tables\Columns\TextColumn::make('status')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('payment_status')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('shipping_method')->searchable(),
                Tables\Columns\TextColumn::make('resi')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\OrderResource\RelationManagers\OrderProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
