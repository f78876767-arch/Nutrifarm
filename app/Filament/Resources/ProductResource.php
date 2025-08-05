<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(['default' => 12])
                    ->schema([
                        Forms\Components\Group::make()
                            ->columnSpan(8)
                            ->schema([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\Textarea::make('description'),
                                Forms\Components\TextInput::make('price')->numeric()->required(),
                                Forms\Components\TextInput::make('discount_price')->numeric()->label('Discount Price')->nullable(),
                                Forms\Components\TextInput::make('stock')->numeric()->required(),
                                Forms\Components\Toggle::make('active')->label('Active')->inline(false),
                                Forms\Components\Select::make('categories')
                                    ->label('Categories')
                                    ->multiple()
                                    ->relationship('categories', 'name')
                                    ->preload()
                                    ->reactive(),
                                Forms\Components\Repeater::make('variants')
                                    ->label('Variants')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')->required()->label('Variant Name'),
                                        Forms\Components\TextInput::make('value')->required()->label('Size/Amount'),
                                        Forms\Components\Select::make('unit')
                                            ->label('Unit')
                                            ->options([
                                                'g' => 'Gram (g)',
                                                'kg' => 'Kilogram (kg)',
                                                'ml' => 'Milliliter (mL)',
                                                'l' => 'Liter (L)',
                                                'other' => 'Other',
                                            ])
                                            ->required()
                                            ->reactive(),
                                        Forms\Components\TextInput::make('custom_unit')
                                            ->label('Custom Unit')
                                            ->visible(fn ($get) => $get('unit') === 'other')
                                            ->nullable(),
                                        Forms\Components\TextInput::make('price')->numeric()->nullable(),
                                        Forms\Components\TextInput::make('stock')->numeric()->nullable(),
                                    ])
                                    ->collapsible()
                                    ->createItemButtonLabel('Add Variant')
                                    ->helperText('Choose a unit or select Other to input a custom unit. Variants are not separate products, but options for this product.'),
                            ]),
                        Forms\Components\Group::make()
                            ->columnSpan(4)
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('Product Image')
                                    ->image()
                                    ->directory('products')
                                    ->disk('public')
                                    ->preserveFilenames()
                                    ->maxSize(2048)
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                                    ->visibility('public')
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->height(40)
                    ->width(40),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(30),
                Tables\Columns\TextColumn::make('price')->money('IDR', true)->sortable(),
                Tables\Columns\TextColumn::make('discount_price')->money('IDR', true)->label('Discount')->sortable(),
                Tables\Columns\TextColumn::make('stock')->sortable(),
                Tables\Columns\IconColumn::make('active')->boolean()->label('Active'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Variants are now managed inline in the main form using a repeater.

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
