@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | Tenants')

@section('content')
    @include('superadmin::layouts.nav')

    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            Tenant Management
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">SaaS tenant overview</small>
        </h1>
    </section>

    <section class="content">
        @include('layouts.partials.error')

        <div class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw-translate-y-0.5 tw-ring-gray-200">
            <div class="tw-p-4 sm:tw-p-5">
                <div class="tw-flex tw-justify-between tw-items-center tw-gap-2.5 tw-mb-4">
                    <strong><i class="fa fa-server margin-r-5"></i> All Tenants</strong>
                    <a href="{{ action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'create']) }}"
                        class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-primary tw-text-white">
                        <i class="fa fa-plus"></i> Add Business / Tenant
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tenants_table">
                        <thead>
                            <tr>
                                <th>Tenant ID</th>
                                <th>Business</th>
                                <th>Owner</th>
                                <th>Email</th>
                                <th>Domains</th>
                                <th>DB Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
@stop

@section('javascript')
    <script>
        $(document).ready(function () {
            $('#tenants_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('superadmin.tenants.index') }}',
                columns: [
                    { data: 'id' },
                    { data: 'business_name', defaultContent: '<em class="text-muted">—</em>' },
                    { data: 'owner_name' },
                    { data: 'owner_email' },
                    { data: 'domains', orderable: false },
                    { data: 'db_status', orderable: false },
                    { data: 'created_at' },
                    { data: 'action', orderable: false, searchable: false },
                ],
            });
        });
    </script>
@endsection
