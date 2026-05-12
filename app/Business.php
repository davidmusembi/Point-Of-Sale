<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'business';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'woocommerce_api_settings'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['woocommerce_api_settings'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'ref_no_prefixes' => 'array',
        'enabled_modules' => 'array',
        'email_settings' => 'array',
        'sms_settings' => 'array',
        'common_settings' => 'array',
        'weighing_scale_setting' => 'array',
    ];

    /**
     * Returns the date formats
     */
    public static function date_formats()
    {
        return [
            'd-m-Y' => 'dd-mm-yyyy',
            'm-d-Y' => 'mm-dd-yyyy',
            'd/m/Y' => 'dd/mm/yyyy',
            'm/d/Y' => 'mm/dd/yyyy',
        ];
    }

    /**
     * Get the owner details
     */
    public function owner()
    {
        return $this->hasOne(\App\User::class, 'id', 'owner_id');
    }

    /**
     * Get the Business currency.
     */
    public function currency()
    {
        return $this->belongsTo(\App\Currency::class);
    }

    /**
     * Get the Business currency.
     */
    public function locations()
    {
        return $this->hasMany(\App\BusinessLocation::class);
    }

    /**
     * Get the Business printers.
     */
    public function printers()
    {
        return $this->hasMany(\App\Printer::class);
    }

    /**
     * Get the Business subscriptions.
     */
    public function subscriptions()
    {
        return $this->hasMany('\Modules\Superadmin\Entities\Subscription');
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Tenant::class, 'tenant_id');
    }

    /**
     * Creates a new business based on the input provided.
     *
     * @return object
     */
    public static function create_business($details)
    {
        if (tenancy()->initialized()) {
            // Logic for adding a business within an existing tenant
            $tenant = tenant();
            $package_id = $tenant->package_id;

            if ($package_id) {
                $package = \DB::connection(config('tenancy.database.central_connection'))
                    ->table('packages')
                    ->where('id', $package_id)
                    ->first();

                if ($package && $package->business_count > 0) {
                    $business_count = Business::count();
                    if ($business_count >= $package->business_count) {
                        throw new \Exception("Business limit reached for your package.");
                    }
                }
            }

            return Business::create($details);
        }

        // Central registration logic (first business for a new account)
        $business = Business::create($details);

        $requestedSlug = request()->get('subdomain');
        $baseSlug = $requestedSlug
            ? \Illuminate\Support\Str::slug($requestedSlug)
            : \Illuminate\Support\Str::slug($business->name);

        // Ensure the subdomain slug is unique across existing domains
        $slug = $baseSlug;
        $counter = 1;
        while (\Stancl\Tenancy\Database\Models\Domain::where('domain', 'like', $slug . '.%')->orWhere('domain', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $package_id = request()->get('package_id');
        $centralDomain = env('APP_DOMAIN', config('tenancy.central_domains.0', 'localhost'));

        $tenant = \App\Tenant::create([
            'owner_id'   => $business->owner_id,
            'package_id' => $package_id,
        ]);

        $tenant->createDomain(['domain' => $slug . '.' . $centralDomain]);

        // Link the central business record to its tenant
        $business->tenant_id = $tenant->id;
        $business->save();

        return $business;
    }

    /**
     * Updates a business based on the input provided.
     *
     * @param  int  $business_id
     * @param  array  $details
     * @return object
     */
    public static function update_business($business_id, $details)
    {
        if (! empty($details)) {
            Business::where('id', $business_id)
                ->update($details);
        }
    }

    public function getBusinessAddressAttribute()
    {
        $location = $this->locations->first();
        $address = $location->landmark.', '.$location->city.
        ', '.$location->state.'<br>'.$location->country.', '.$location->zip_code;

        return $address;
    }
}
