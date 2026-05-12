<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Tenant;
use Illuminate\Http\Request;
use Stancl\Tenancy\Database\Models\Domain;

class DomainController extends BaseController
{
    public function store(Request $request)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'domain'    => 'required|string|max:255|unique:domains,domain',
        ]);

        try {
            $tenant = Tenant::findOrFail($request->tenant_id);
            $tenant->createDomain(['domain' => $request->domain]);

            $output = ['success' => 1, 'msg' => 'Domain added successfully.'];
        } catch (\Exception $e) {
            \Log::error('Domain creation error: ' . $e->getMessage());
            $output = ['success' => 0, 'msg' => 'Failed to add domain: ' . $e->getMessage()];
        }

        return redirect()->back()->with('status', $output);
    }

    public function destroy(int $id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            Domain::findOrFail($id)->delete();
            $output = ['success' => 1, 'msg' => 'Domain removed successfully.'];
        } catch (\Exception $e) {
            \Log::error('Domain deletion error: ' . $e->getMessage());
            $output = ['success' => 0, 'msg' => 'Failed to remove domain.'];
        }

        return redirect()->back()->with('status', $output);
    }
}
