<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
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

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Discounts';
    protected static ?string $navigationGroup = 'Promotions';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Discount Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Summer Sale 2024'),
                                
                                Forms\Components\Select::make('type')
                                    ->required()
                                    ->options([
                                        'percentage' => 'Percentage Off',
                                        'fixed_amount' => 'Fixed Amount Off',
                                        'buy_x_get_y' => 'Buy X Get Y Free',
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('value', null)),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Describe this discount offer...'),
                    ]),

                Section::make('Discount Settings')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('value')
                                    ->required()
                                    ->numeric()
                                    ->label(function (callable $get) {
                                        return match ($get('type')) {
                                            'percentage' => 'Discount Percentage (%)',
                                            'fixed_amount' => 'Discount Amount ($)',
                                            'buy_x_get_y' => 'Minimum Quantity to Buy',
                                            default => 'Value',
                                        };
                                    })
                                    ->placeholder(function (callable $get) {
                                        return match ($get('type')) {
                                            'percentage' => 'e.g., 25 (for 25%)',
                                            'fixed_amount' => 'e.g., 10.00',
                                            'buy_x_get_y' => 'e.g., 2 (buy 2)',
                                            default => 'Enter value',
                                        };
                                    })
                                    ->suffix(function (callable $get) {
                                        return match ($get('type')) {
                                            'percentage' => '%',
                                            'fixed_amount' => '$',
                                            default => null,
                                        };
                                    }),

                                Forms\Components\TextInput::make('get_quantity')
                                    ->numeric()
                                    ->label('Get Quantity Free')
                                    ->placeholder('e.g., 1 (get 1 free)')
                                    ->visible(fn (callable $get) => $get('type') === 'buy_x_get_y')
                                    ->required(fn (callable $get) => $get('type') === 'buy_x_get_y'),

                                Forms\Components\TextInput::make('min_purchase_amount')
                                    ->numeric()
                                    ->label('Minimum Purchase Amount')
                                    ->placeholder('e.g., 50.00')
                                    ->prefix('$'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('max_discount_amount')
                                    ->numeric()
                                    ->label('Maximum Discount Amount')
                                    ->placeholder('e.g., 100.00')
                                    ->prefix('$')
                                    ->helperText('Leave empty for no limit'),

                                Forms\Components\TextInput::make('usage_limit')
                                    ->numeric()
                                    ->label('Usage Limit')
                                    ->placeholder('e.g., 100')
                                    ->helperText('Leave empty for unlimited use'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ]),
                    ]),

                Section::make('Schedule')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('starts_at')
                                    ->label('Start Date & Time')
                                    ->placeholder('Leave empty to start immediately'),

                                Forms\Components\DateTimePicker::make('ends_at')
                                    ->label('End Date & Time')
                                    ->placeholder('Leave empty for no end date')
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
                            ->placeholder('Select products for this discount')
                            ->helperText('Leave empty to apply manually later'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'percentage',
                        'success' => 'fixed_amount',
                        'warning' => 'buy_x_get_y',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Percentage',
                        'fixed_amount' => 'Fixed Amount',
                        'buy_x_get_y' => 'Buy X Get Y',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('value')
                    ->label('Discount Value')
                    ->formatStateUsing(function ($record) {
                        return match ($record->type) {
                            'percentage' => $record->value . '%',
                            'fixed_amount' => '$' . number_format($record->value, 2),
                            'buy_x_get_y' => "Buy {$record->value}, Get {$record->get_quantity}",
                            default => $record->value,
                        };
                    }),

                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(fn ($record) => $record->status)
                    ->colors([
                        'success' => 'Active',
                        'warning' => 'Scheduled',
                        'danger' => ['Expired', 'Used Up'],
                        'gray' => 'Inactive',
                    ]),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('Used')
                    ->formatStateUsing(function ($record) {
                        $limit = $record->usage_limit ? "/{$record->usage_limit}" : '';
                        return $record->used_count . $limit;
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (!$record->usage_limit) return 'gray';
                        $percentage = ($record->used_count / $record->usage_limit) * 100;
                        return $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');
                    }),

                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->placeholder('Immediate'),

                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->placeholder('No end date'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed_amount' => 'Fixed Amount',
                        'buy_x_get_y' => 'Buy X Get Y',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'scheduled' => 'Scheduled',
                        'expired' => 'Expired',
                        'inactive' => 'Inactive',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($query, $status) {
                            $now = now();
                            return match ($status) {
                                'active' => $query->where('is_active', true)
                                    ->where(function ($q) use ($now) {
                                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                                    })
                                    ->where(function ($q) use ($now) {
                                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                                    }),
                                'scheduled' => $query->where('is_active', true)
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
