<?php

namespace App\Filament\Superadmin\Resources;

use App\Filament\Superadmin\Resources\SubscriptionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Superadmin\Entities\Subscription;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Subscriptions';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Subscription';

    public static function form(Form $form): Form
    {
        $businesses = \App\Business::orderBy('name')->pluck('name', 'id')->toArray();
        $packages   = \Modules\Superadmin\Entities\Package::orderBy('name')->pluck('name', 'id')->toArray();

        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\Select::make('business_id')
                    ->label('Business')
                    ->options($businesses)
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('package_id')
                    ->label('Package')
                    ->options($packages)
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('status')
                    ->options(Subscription::package_subscription_status())
                    ->required(),

                Forms\Components\DatePicker::make('start_date')
                    ->required(),

                Forms\Components\DatePicker::make('end_date')
                    ->required(),

                Forms\Components\DatePicker::make('trial_end_date')
                    ->label('Trial End Date')
                    ->nullable(),

                Forms\Components\TextInput::make('paid_amount')
                    ->numeric()
                    ->prefix('$')
                    ->default(0),

                Forms\Components\Select::make('paid_via')
                    ->label('Paid Via')
                    ->options([
                        'cash'     => 'Cash',
                        'stripe'   => 'Stripe',
                        'paypal'   => 'PayPal',
                        'razorpay' => 'Razorpay',
                        'offline'  => 'Offline',
                    ])
                    ->nullable(),

                Forms\Components\TextInput::make('payment_transaction_id')
                    ->label('Transaction ID')
                    ->nullable(),

                Forms\Components\TextInput::make('coupon_code')
                    ->label('Coupon Code')
                    ->nullable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('package.name')
                    ->label('Package')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'waiting'  => 'warning',
                        'declined' => 'danger',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Expires')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_via')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Subscription::package_subscription_status()),

                Tables\Filters\SelectFilter::make('package_id')
                    ->label('Package')
                    ->relationship('package', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Subscription $record) => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->action(fn (Subscription $record) => $record->update(['status' => 'approved'])),

                Tables\Actions\Action::make('decline')
                    ->label('Decline')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Subscription $record) => $record->status !== 'declined')
                    ->requiresConfirmation()
                    ->action(fn (Subscription $record) => $record->update(['status' => 'declined'])),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit'   => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
