<?php

namespace App\Filament\Superadmin\Resources\BusinessResource\Pages;

use App\Business;
use App\Filament\Superadmin\Resources\BusinessResource;
use App\User;
use App\Utils\BusinessUtil;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Superadmin\Entities\Package;
use Modules\Superadmin\Notifications\BusinessWelcomeNotification;


class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $businessUtil = app(BusinessUtil::class);
        $business     = null;
        $user         = null;
        $plain_password = null;

        DB::beginTransaction();
        try {
            $pool           = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $plain_password = substr(str_shuffle(str_repeat($pool, 3)), 0, 12);

            $owner_details = [
                'surname'    => $data['surname']    ?? '',
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name']  ?? '',
                'username'   => $data['username'],
                'email'      => $data['email'],
                'password'   => $plain_password,
                'language'   => env('APP_LOCALE', 'en'),
            ];
            $user = User::create_user($owner_details);

            $business_details = [
                'name'               => $data['name'],
                'currency_id'        => $data['currency_id'],
                'time_zone'          => $data['time_zone'],
                'accounting_method'  => $data['accounting_method'],
                'fy_start_month'     => $data['fy_start_month'],
                'start_date'         => $data['start_date'] ?? null,
                'logo'               => $data['logo']      ?? null,
                'owner_id'           => $user->id,
                'enabled_modules'    => ['purchases', 'add_sale', 'pos_sale', 'stock_transfers', 'stock_adjustment', 'expenses'],
                'created_by'         => auth()->id(),
            ];

            $business = $businessUtil->createNewBusiness($business_details);

            $user->business_id = $business->id;
            $user->save();

            // Subscription
            if (!empty($data['package_id']) && !empty($data['paid_via'])) {
                $package = Package::find($data['package_id']);
                if ($package) {
                    \Modules\Superadmin\Entities\Subscription::create([
                        'business_id'            => $business->id,
                        'package_id'             => $package->id,
                        'status'                 => 'approved',
                        'start_date'             => now(),
                        'end_date'               => now()->addMonths($package->interval_count ?? 1),
                        'paid_amount'            => $package->price,
                        'paid_via'               => $data['paid_via'],
                        'payment_transaction_id' => $data['payment_transaction_id'] ?? null,
                        'package_details'        => $package->toArray(),
                        'created_id'             => auth()->id(),
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        // Tenant provisioning is handled by SubscriptionObserver when the
        // subscription status becomes 'approved'.

        // Send welcome email
        try {
            if (class_exists(\Modules\Superadmin\Notifications\BusinessWelcomeNotification::class)) {
                $user->notify(new BusinessWelcomeNotification(
                    $business->name,
                    url('/login'),
                    $user->username,
                    $plain_password
                ));
            }
        } catch (\Exception $e) {
            \Log::warning('Welcome email failed for business ' . $business->id . ': ' . $e->getMessage());
        }

        return $business;
    }
}
