<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Business;
use App\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;
use App\Jobs\SeedTenantData;

class TenantController extends BaseController
{
    public function index()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $tenants = Tenant::with('domains')
                ->leftJoin('business', 'business.tenant_id', '=', 'tenants.id')
                ->leftJoin('users as u', 'u.id', '=', 'tenants.owner_id')
                ->select(
                    'tenants.id',
                    'tenants.owner_id',
                    'tenants.package_id',
                    'tenants.created_at',
                    'business.id as business_id',
                    'business.name as business_name',
                    'business.is_active',
                    DB::raw("CONCAT(COALESCE(u.surname,''), ' ', COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,'')) as owner_name"),
                    'u.email as owner_email'
                );

            return \Yajra\DataTables\Facades\DataTables::of($tenants)
                ->addColumn('db_status', function ($row) {
                    $provisioned = $this->isDbProvisioned($row->id);
                    if ($provisioned) {
                        return '<span class="label bg-green">Provisioned</span>';
                    }
                    return '<span class="label bg-red">Not Provisioned</span>';
                })
                ->addColumn('domains', function ($row) {
                    $tenant = Tenant::find($row->id);
                    if (! $tenant) {
                        return '-';
                    }
                    return $tenant->domains->pluck('domain')->implode('<br>') ?: '-';
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    if ($row->business_id) {
                        $html .= '<a href="' . action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'show'], [$row->business_id]) . '"
                            class="tw-m-0.5 tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info">Manage</a>';
                    }
                    $provisionRoute = route('superadmin.tenants.provision', $row->id);
                    $migrateRoute = route('superadmin.tenants.migrate', $row->id);
                    $html .= '<form method="POST" action="' . $provisionRoute . '" class="tw-inline-block" onsubmit="return confirm(\'Provision DB for this tenant? This will create and migrate the database.\')">
                        ' . csrf_field() . '
                        <button type="submit" class="tw-m-0.5 tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent">Provision DB</button>
                    </form>
                    <form method="POST" action="' . $migrateRoute . '" class="tw-inline-block" onsubmit="return confirm(\'Run pending migrations on this tenant DB?\')">
                        ' . csrf_field() . '
                        <button type="submit" class="tw-m-0.5 tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-warning">Run Migrations</button>
                    </form>';
                    return $html;
                })
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->rawColumns(['db_status', 'domains', 'action', 'created_at'])
                ->make(true);
        }

        return view('superadmin::tenants.index');
    }

    public function provision(string $id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $tenant = Tenant::findOrFail($id);

        try {
            dispatch_sync(new CreateDatabase($tenant));
            dispatch_sync(new MigrateDatabase($tenant));
            dispatch_sync(new SeedTenantData($tenant));

            $output = ['success' => 1, 'msg' => 'Tenant database provisioned and seeded successfully.'];
        } catch (\Exception $e) {
            \Log::error('Tenant provision error: ' . $e->getMessage());
            $output = ['success' => 0, 'msg' => 'Provisioning failed: ' . $e->getMessage()];
        }

        return redirect()->back()->with('status', $output);
    }

    public function migrate(string $id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $tenant = Tenant::findOrFail($id);

        try {
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--force' => true,
            ]);

            $output = ['success' => 1, 'msg' => 'Tenant migrations ran successfully.'];
        } catch (\Exception $e) {
            \Log::error('Tenant migrate error: ' . $e->getMessage());
            $output = ['success' => 0, 'msg' => 'Migration failed: ' . $e->getMessage()];
        }

        return redirect()->back()->with('status', $output);
    }

    private function isDbProvisioned(string $tenantId): bool
    {
        $prefix = config('tenancy.database.prefix', 'tenant');
        $suffix = config('tenancy.database.suffix', '');
        $dbName = $prefix . $tenantId . $suffix;

        $result = DB::select(
            'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$dbName]
        );

        return ! empty($result);
    }
}
