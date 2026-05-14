<?php

namespace App\Filament\Superadmin\Resources;

use App\Filament\Superadmin\Resources\PackageResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Superadmin\Entities\Package;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $navigationLabel = 'Packages';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Package Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('price')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->required(),

                    Forms\Components\Select::make('interval')
                        ->options(['day' => 'Day', 'month' => 'Month', 'year' => 'Year'])
                        ->default('month')
                        ->required(),

                    Forms\Components\TextInput::make('interval_count')
                        ->label('Interval Count')
                        ->numeric()
                        ->default(1)
                        ->required(),

                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('trial_days')
                        ->label('Trial Days')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(0),
                ]),

            Forms\Components\Section::make('Limits')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('location_count')
                        ->label('Max Locations (0 = unlimited)')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('user_count')
                        ->label('Max Users (0 = unlimited)')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('product_count')
                        ->label('Max Products (0 = unlimited)')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('invoice_count')
                        ->label('Max Invoices (0 = unlimited)')
                        ->numeric()
                        ->default(0),
                ]),

            Forms\Components\Section::make('Flags')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    Forms\Components\Toggle::make('is_private')
                        ->label('Private (SuperAdmin Only)')
                        ->default(false),

                    Forms\Components\Toggle::make('is_one_time')
                        ->label('One-Time Subscription')
                        ->default(false),

                    Forms\Components\Toggle::make('mark_package_as_popular')
                        ->label('Mark as Popular')
                        ->default(false),
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
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('interval_count')
                    ->label('Duration')
                    ->formatStateUsing(fn ($record) => $record->interval_count . ' ' . $record->interval)
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('interval_count', $direction)),

                Tables\Columns\TextColumn::make('trial_days')
                    ->label('Trial')
                    ->suffix(' days')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_private')
                    ->label('Private')
                    ->boolean(),

                Tables\Columns\IconColumn::make('mark_package_as_popular')
                    ->label('Popular')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'edit'   => Pages\EditPackage::route('/{record}/edit'),
        ];
    }
}
