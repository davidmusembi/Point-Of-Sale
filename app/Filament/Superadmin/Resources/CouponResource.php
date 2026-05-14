<?php

namespace App\Filament\Superadmin\Resources;

use App\Filament\Superadmin\Resources\CouponResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Superadmin\Entities\SuperadminCoupon;

class CouponResource extends Resource
{
    protected static ?string $model = SuperadminCoupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Coupons';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Coupon';

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Coupon Code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->helperText('Case-insensitive at checkout'),

                Forms\Components\Select::make('discount_type')
                    ->label('Discount Type')
                    ->options(['fixed' => 'Fixed Amount', 'percentage' => 'Percentage'])
                    ->required()
                    ->live(),

                Forms\Components\TextInput::make('discount')
                    ->label('Discount Value')
                    ->numeric()
                    ->required()
                    ->suffix(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? '%' : null)
                    ->prefix(fn (Forms\Get $get) => $get('discount_type') === 'fixed' ? '$' : null),

                Forms\Components\TextInput::make('usage_limit')
                    ->label('Usage Limit (blank = unlimited)')
                    ->numeric()
                    ->nullable(),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expiry Date')
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('discount_type')
                    ->badge()
                    ->color(fn ($state) => $state === 'percentage' ? 'info' : 'success'),

                Tables\Columns\TextColumn::make('discount')
                    ->formatStateUsing(fn ($record) => $record->discount_type === 'percentage'
                        ? $record->discount . '%'
                        : '$' . number_format($record->discount, 2)),

                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Limit')
                    ->default('∞'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Status'),
            ])
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
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
