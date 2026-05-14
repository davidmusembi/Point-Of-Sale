<?php

namespace App\Observers;

use App\Business;
use App\Utils\BusinessUtil;
use Modules\Superadmin\Entities\Subscription;

class SubscriptionObserver
{
    public function created(Subscription $subscription): void
    {
        if ($subscription->status === 'approved') {
            $businessId = $subscription->business_id;
            \DB::afterCommit(fn () => $this->maybeProvisionTenant($businessId));
        }
    }

    public function updated(Subscription $subscription): void
    {
        if ($subscription->wasChanged('status') && $subscription->status === 'approved') {
            $businessId = $subscription->business_id;
            \DB::afterCommit(fn () => $this->maybeProvisionTenant($businessId));
        }
    }

    private function maybeProvisionTenant(int $businessId): void
    {
        $business = Business::find($businessId);
        if (!$business || $business->tenant_id) return;

        try {
            $centralDomain = env('APP_DOMAIN', 'localhost');
            $base          = \Illuminate\Support\Str::slug($business->name);
            $slug          = $base;
            $counter       = 1;

            while (\Stancl\Tenancy\Database\Models\Domain::where('domain', $slug . '.' . $centralDomain)->exists()) {
                $slug = $base . '-' . $counter++;
            }

            // Creating the tenant fires TenantCreated which triggers the
            // TenancyServiceProvider pipeline: CreateDatabase → MigrateDatabase → SeedTenantData
            $tenant = \App\Tenant::create([
                'owner_id'   => $business->owner_id,
                'package_id' => null,
            ]);

            $tenant->createDomain(['domain' => $slug . '.' . $centralDomain]);

            $business->tenant_id = $tenant->id;
            $business->saveQuietly();

            // Add the default business location inside the provisioned tenant DB
            $tenant->run(function () use ($business) {
                $location = app(BusinessUtil::class)->addLocation($business->id, [
                    'name'             => $business->name,
                    'country'          => '',
                    'state'            => '',
                    'city'             => '',
                    'zip_code'         => '',
                    'landmark'         => '',
                    'website'          => '',
                    'mobile'           => '',
                    'alternate_number' => '',
                ]);
                \Spatie\Permission\Models\Permission::create(['name' => 'location.' . $location->id]);
            });

            \Log::info("Tenant provisioned for business {$businessId}: {$slug}.{$centralDomain}");
        } catch (\Exception $e) {
            \Log::error('Tenant provisioning failed for business ' . $businessId . ': ' . $e->getMessage());
        }
    }
}
