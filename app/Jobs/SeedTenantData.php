<?php

namespace App\Jobs;

use App\Business;
use App\User;
use App\Utils\BusinessUtil;
use Database\Seeders\TenantSeeder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\Tenant;

class SeedTenantData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle()
    {
        $this->tenant->run(function () {
            $owner_id = $this->tenant->owner_id;
            $centralConnection = config('tenancy.database.central_connection', 'mysql');

            $central_business = \DB::connection($centralConnection)
                ->table('business')
                ->where('owner_id', $owner_id)
                ->first();

            $central_owner = \DB::connection($centralConnection)
                ->table('users')
                ->where('id', $owner_id)
                ->first();

            // Seed currencies first — business.currency_id FK requires them to exist
            (new TenantSeeder())->run();

            // FK insertion order:
            //   currencies must exist first (seeded above)
            //   users.business_id → business.id, business.owner_id → users.id (circular)
            //   Break the cycle: create user with business_id=null, then business, then update user
            if ($central_owner) {
                $ownerData = (array) $central_owner;
                $ownerData['business_id'] = null;
                User::forceCreate($ownerData);
            }

            if ($central_business) {
                $bizData = (array) $central_business;
                unset($bizData['tenant_id']); // central-only column, absent in tenant DB
                Business::forceCreate($bizData);
            }

            if ($central_owner && $central_business) {
                User::where('id', $central_owner->id)
                    ->update(['business_id' => $central_business->id]);
            }

            // Create default roles, walk-in customer, invoice layout etc.
            if ($central_business && $central_owner) {
                // Spatie Permission caches from central context; reset so it reads tenant permissions
                app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
                app(BusinessUtil::class)->newBusinessDefaultResources(
                    $central_business->id,
                    $central_owner->id
                );
            }
        });
    }
}
