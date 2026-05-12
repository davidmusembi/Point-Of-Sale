<?php

namespace App\Jobs;

use App\Business;
use App\User;
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
            
            // Get business details from central DB associated with this owner
            $central_business = \DB::connection(config('tenancy.database.central_connection'))
                ->table('business')
                ->where('owner_id', $owner_id)
                ->first();

            if ($central_business) {
                // Insert into tenant business table
                Business::create((array) $central_business);
            }

            // Get owner details from central DB
            $owner = \DB::connection(config('tenancy.database.central_connection'))
                ->table('users')
                ->where('id', $owner_id)
                ->first();

            if ($owner) {
                // Insert into tenant users table
                User::create((array) $owner);
            }
        });
    }
}
