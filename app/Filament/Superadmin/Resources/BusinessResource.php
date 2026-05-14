<?php

namespace App\Filament\Superadmin\Resources;

use App\Business;
use App\Filament\Superadmin\Resources\BusinessResource\Pages;
use App\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Businesses';

    protected static ?string $navigationGroup = 'SaaS Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        $currencies   = \App\Currency::orderBy('currency')->get()->mapWithKeys(fn ($c) => [$c->id => "{$c->currency} ({$c->code}) — {$c->country}"])->toArray();
        $timezones    = collect(timezone_identifiers_list())->mapWithKeys(fn ($tz) => [$tz => $tz])->toArray();
        $months       = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        $packages = \Modules\Superadmin\Entities\Package::active()->orderBy('name')->pluck('name', 'id')->prepend('— None —', '')->toArray();

        return $form->schema([
            Forms\Components\Section::make('Business Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Business Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('currency_id')
                        ->label('Currency')
                        ->options($currencies)
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('time_zone')
                        ->label('Timezone')
                        ->options($timezones)
                        ->default('America/Chicago')
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('accounting_method')
                        ->label('Accounting Method')
                        ->options(['fifo' => 'FIFO', 'lifo' => 'LIFO', 'avco' => 'AVCO'])
                        ->default('fifo')
                        ->required(),

                    Forms\Components\Select::make('fy_start_month')
                        ->label('Financial Year Start')
                        ->options($months)
                        ->default(1)
                        ->required(),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('Business Start Date')
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->closeOnDateSelection(),

                    Forms\Components\TextInput::make('subdomain')
                        ->label('Subdomain')
                        ->helperText('Auto-generated if left blank')
                        ->maxLength(100),

                    Forms\Components\FileUpload::make('logo')
                        ->label('Business Logo')
                        ->image()
                        ->directory('business_logos')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Owner Account')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('surname')
                        ->label('Prefix')
                        ->options(['' => '', 'Mr' => 'Mr', 'Mrs' => 'Mrs', 'Ms' => 'Ms', 'Dr' => 'Dr'])
                        ->default(''),

                    Forms\Components\TextInput::make('first_name')
                        ->label('First Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('last_name')
                        ->label('Last Name')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('username')
                        ->label('Username')
                        ->required()
                        ->minLength(4)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->columnSpanFull()
                        ->helperText('Login credentials will be sent to this email.'),
                ]),

            Forms\Components\Section::make('Initial Subscription')
                ->description('Optional — assign a package at creation time')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('package_id')
                        ->label('Package')
                        ->options($packages)
                        ->live(),

                    Forms\Components\Select::make('paid_via')
                        ->label('Paid Via')
                        ->options(static::paymentGatewayOptions())
                        ->visible(fn (Forms\Get $get) => !empty($get('package_id'))),

                    Forms\Components\TextInput::make('payment_transaction_id')
                        ->label('Transaction ID')
                        ->visible(fn (Forms\Get $get) => !empty($get('package_id'))),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('name')
                    ->label('Business')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('owner.email')
                    ->label('Owner Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('owner.contact_number')
                    ->label('Owner Phone')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('activeSubscription.package.name')
                    ->label('Package')
                    ->default('—')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options(['1' => 'Active', '0' => 'Inactive']),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Business $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Business $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Business $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (Business $record) => $record->update(['is_active' => !$record->is_active])),

                Tables\Actions\Action::make('update_password')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->url(fn (Business $record) => url("/superadmin/business/{$record->id}/edit"))
                    ->openUrlInNewTab(),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
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
            'index'  => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'view'   => Pages\ViewBusiness::route('/{record}'),
            'edit'   => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['owner']);
    }

    protected static function paymentGatewayOptions(): array
    {
        $gateways = [];

        if (env('STRIPE_PUB_KEY') && env('STRIPE_SECRET_KEY')) {
            $gateways['stripe'] = 'Stripe';
        }
        if (env('PAYPAL_CLIENT_ID') && env('PAYPAL_APP_SECRET')) {
            $gateways['paypal'] = 'PayPal';
        }
        if (env('RAZORPAY_KEY_ID') && env('RAZORPAY_KEY_SECRET')) {
            $gateways['razorpay'] = 'Razor Pay';
        }
        if (config('pesapal.consumer_key') && config('pesapal.consumer_secret')) {
            $gateways['pesapal'] = 'PesaPal';
        }
        if (env('FLUTTERWAVE_PUBLIC_KEY') && env('FLUTTERWAVE_SECRET_KEY') && env('FLUTTERWAVE_ENCRYPTION_KEY')) {
            $gateways['flutterwave'] = 'Flutterwave';
        }
        if (env('MY_FATOORAH_API_KEY') && env('MY_FATOORAH_COUNTRY_ISO')) {
            $gateways['myfatoorah'] = 'My Fatoorah';
        }
        if (\App\System::getProperty('enable_offline_payment')) {
            $gateways['offline'] = 'Offline';
        }

        // Always show all options in superadmin context — gateways may be
        // configured after business creation. Fall back to full list if none match.
        if (empty($gateways)) {
            $gateways = [
                'stripe'      => 'Stripe',
                'paypal'      => 'PayPal',
                'razorpay'    => 'Razor Pay',
                'pesapal'     => 'PesaPal',
                'flutterwave' => 'Flutterwave',
                'myfatoorah'  => 'My Fatoorah',
                'offline'     => 'Offline',
            ];
        }

        return $gateways;
    }
}
