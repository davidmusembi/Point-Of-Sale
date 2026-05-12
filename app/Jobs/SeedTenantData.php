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
            $centralConnection = config('tenancy.database.central_connection', 'central');

            // Copy business from central DB to tenant DB
            $central_business = \DB::connection($centralConnection)
                ->table('business')
                ->where('owner_id', $owner_id)
                ->first();

            if ($central_business) {
                Business::create((array) $central_business);
            }

            // Copy owner from central DB to tenant DB
            $owner = \DB::connection($centralConnection)
                ->table('users')
                ->where('id', $owner_id)
                ->first();

            if ($owner) {
                User::create((array) $owner);
            }

            // Seed currencies and core permissions into the fresh tenant DB
            (new TenantSeeder())->run();

            // Create default roles, walk-in customer, invoice layout etc.
            if ($central_business && $owner) {
                app(BusinessUtil::class)->newBusinessDefaultResources(
                    $central_business->id,
                    $owner->id
                );
            }
        });
    }
}
