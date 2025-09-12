<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlashSaleResource\Pages;
use App\Models\FlashSale;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FlashSaleResource extends Resource
{
    protected static ?string $model = FlashSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationLabel = 'Flash Sales';
    protected static ?string $navigationGroup = 'Promotions';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Flash Sale Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., 24-Hour Flash Sale'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Describe this flash sale...'),
                    ]),

                Section::make('Discount Settings')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('discount_percentage')
                                    ->required()
                                    ->numeric()
                                    ->label('Discount Percentage (%)')
                                    ->placeholder('e.g., 50 (for 50% off)')
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100),

                                Forms\Components\TextInput::make('max_discount_amount')
                                    ->numeric()
                                    ->label('Maximum Discount Amount')
                                    ->placeholder('e.g., 200.00')
                                    ->prefix('$')
                                    ->helperText('Leave empty for no limit'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ]),
                    ]),

                Section::make('Quantity Limits')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('max_quantity')
                                    ->numeric()
                                    ->label('Maximum Quantity for Sale')
                                    ->placeholder('e.g., 100')
                                    ->helperText('Total items that can be sold at flash sale price'),

                                Forms\Components\TextInput::make('sold_quantity')
                                    ->numeric()
                                    ->label('Already Sold Quantity')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),

                Section::make('Schedule')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('starts_at')
                                    ->required()
                                    ->label('Start Date & Time'),

                                Forms\Components\DateTimePicker::make('ends_at')
                                    ->required()
                                    ->label('End Date & Time')
                                    ->after('starts_at'),
                            ]),
                    ]),

                Section::make('Apply to Products')
                    ->schema([
                        Forms\Components\Select::make('products')
                            ->relationship('products', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->placeholder('Select products for this flash sale')
                            ->helperText('Select specific products for this flash sale'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Discount')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(fn ($record) => $record->status)
                    ->colors([
                        'success' => 'Active',
                        'warning' => 'Upcoming',
                        'danger' => ['Expired', 'Sold Out'],
                        'gray' => 'Inactive',
                    ]),

                Tables\Columns\ProgressBarColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        return $record->max_quantity ? 
                            ($record->sold_quantity / $record->max_quantity) * 100 : 0;
                    })
                    ->color(function ($record) {
                        if (!$record->max_quantity) return 'gray';
                        $percentage = ($record->sold_quantity / $record->max_quantity) * 100;
                        return $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');
                    }),

                Tables\Columns\TextColumn::make('sold_quantity')
                    ->label('Sold/Max')
                    ->formatStateUsing(function ($record) {
                        $max = $record->max_quantity ? "/{$record->max_quantity}" : '';
                        return $record->sold_quantity . $max;
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (!$record->max_quantity) return 'gray';
                        $percentage = ($record->sold_quantity / $record->max_quantity) * 100;
                        return $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');
                    }),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Start Time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('End Time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'upcoming' => 'Upcoming',
                        'expired' => 'Expired',
                        'inactive' => 'Inactive',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($query, $status) {
                            $now = now();
                            return match ($status) {
                                'active' => $query->where('is_active', true)
                                    ->where('starts_at', '<=', $now)
                                    ->where('ends_at', '>=', $now),
                                'upcoming' => $query->where('is_active', true)
                                    ->where('starts_at', '>', $now),
                                'expired' => $query->where('is_active', true)
                                    ->where('ends_at', '<', $now),
                                'inactive' => $query->where('is_active', false),
                            };
                        });
                    }),

                Filter::make('has_products')
                    ->label('Has Products')
                    ->query(fn (Builder $query): Builder => $query->has('products')),

                Filter::make('has_stock')
                    ->label('Has Stock Available')
                    ->query(function (Builder $query): Builder {
                        return $query->whereRaw('sold_quantity < max_quantity OR max_quantity IS NULL');
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('starts_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlashSales::route('/'),
            'create' => Pages\CreateFlashSale::route('/create'),
            'edit' => Pages\EditFlashSale::route('/{record}/edit'),
        ];
    }
}
