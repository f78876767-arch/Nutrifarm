<?php

namespace App\Filament\Resources;

use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Orders & Sales';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_no')->label('Invoice')->disabled(),
                        Forms\Components\TextInput::make('external_id')->label('Order No')->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'expired' => 'Expired',
                            ])->required(),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'expired' => 'Expired',
                            ])->required(),
                        Forms\Components\TextInput::make('shipping_method')->label('Shipping'),
                        Forms\Components\TextInput::make('resi')->label('AWB / Resi'),
                        Forms\Components\Textarea::make('cancel_reason')->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('external_id')
                    ->label('Order No')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => ['cancelled', 'expired'],
                        'primary' => 'processing',
                    ]),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => ['failed', 'expired'],
                    ]),
                Tables\Columns\TextColumn::make('shipping_method')
                    ->label('Shipping')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('resi')
                    ->label('AWB')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'expired' => 'Expired',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        \Filament\Infolists\Components\TextEntry::make('invoice_no')->label('Invoice'),
                        \Filament\Infolists\Components\TextEntry::make('external_id')->label('Order No'),
                        \Filament\Infolists\Components\TextEntry::make('user.name')->label('Customer'),
                        \Filament\Infolists\Components\TextEntry::make('status')->badge(),
                        \Filament\Infolists\Components\TextEntry::make('payment_status')->badge(),
                        \Filament\Infolists\Components\TextEntry::make('total')->money('idr'),
                        \Filament\Infolists\Components\TextEntry::make('shipping_method')->label('Shipping'),
                        \Filament\Infolists\Components\TextEntry::make('resi')->label('AWB / Resi'),
                        \Filament\Infolists\Components\TextEntry::make('created_at')->dateTime(),
                    ]),
                Tables\Actions\EditAction::make()
                    ->form(fn () => static::form(app(\Filament\Forms\Form::class))->getSchema()),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\OrderResource\Pages\ListOrders::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'invoice_no',
            'external_id',
            'payment_status',
            'resi',
            'user.name',
        ];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        /** @var Order $record */
        return [
            'Customer' => optional($record->user)->name,
            'Status' => $record->status,
            'Payment' => $record->payment_status,
            'Total' => number_format((float) $record->total, 0, ',', '.'),
        ];
    }
}
