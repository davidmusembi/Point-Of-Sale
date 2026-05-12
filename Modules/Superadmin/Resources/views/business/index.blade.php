@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | Business')

@section('content')
@include('superadmin::layouts.nav')

<section class="content-header tw-px-4 sm:tw-px-6 tw-pb-0">
    <div class="tw-flex tw-items-center tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">@lang('superadmin::lang.all_business')</h1>
            <p class="tw-text-sm tw-text-gray-500 tw-mt-0.5">@lang('superadmin::lang.manage_business')</p>
        </div>
        <a href="{{ action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'create']) }}"
           class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-rounded-lg tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-text-sm tw-font-semibold tw-shadow tw-transition-colors tw-no-underline">
            <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            @lang('messages.add')
        </a>
    </div>
</section>

<section class="content tw-px-4 sm:tw-px-6 tw-mt-5">

    {{-- Filters --}}
    <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm tw-mb-5">
        <div class="tw-px-5 tw-py-3 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-2 tw-cursor-pointer"
             data-toggle="collapse" data-target="#businessFilters">
            <svg class="tw-w-4 tw-h-4 tw-text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <span class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">@lang('report.filters')</span>
        </div>
        <div id="businessFilters" class="collapse in">
            <div class="tw-p-5 tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-5 tw-gap-4">
                <div>
                    <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.packages')</label>
                    {!! Form::select('package_id', $packages, null, ['class' => 'form-control input-sm select2', 'placeholder' => __('lang_v1.all'), 'id' => 'package_id']) !!}
                </div>
                <div>
                    <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.subscription_status')</label>
                    {!! Form::select('subscription_status', $subscription_statuses, null, ['class' => 'form-control input-sm select2', 'placeholder' => __('lang_v1.all'), 'id' => 'subscription_status']) !!}
                </div>
                <div>
                    <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('sale.status')</label>
                    {!! Form::select('is_active', ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')], null, ['class' => 'form-control input-sm select2', 'placeholder' => __('lang_v1.all'), 'id' => 'is_active']) !!}
                </div>
                <div>
                    <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.last_transaction_date')</label>
                    {!! Form::select('last_transaction_date', $last_transaction_date, null, ['class' => 'form-control input-sm select2', 'placeholder' => __('messages.please_select'), 'id' => 'last_transaction_date']) !!}
                </div>
                <div>
                    <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.no_transaction_since')</label>
                    {!! Form::select('no_transaction_since', $last_transaction_date, null, ['class' => 'form-control input-sm select2', 'placeholder' => __('messages.please_select'), 'id' => 'no_transaction_since']) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
        <div class="tw-p-5">
            @can('superadmin')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="superadmin_business_table">
                    <thead>
                        <tr>
                            <th>@lang('superadmin::lang.registered_on')</th>
                            <th>@lang('superadmin::lang.business_name')</th>
                            <th>@lang('business.owner')</th>
                            <th>@lang('business.email')</th>
                            <th>@lang('superadmin::lang.owner_number')</th>
                            <th>@lang('superadmin::lang.business_contact_number')</th>
                            <th>@lang('business.address')</th>
                            <th>@lang('sale.status')</th>
                            <th>@lang('superadmin::lang.current_subscription')</th>
                            <th>@lang('business.created_by')</th>
                            <th>@lang('superadmin::lang.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
            @endcan
        </div>
    </div>

</section>
@endsection

@section('javascript')
<script>
$(document).ready(function () {
    $('.select2').select2();

    var superadmin_business_table = $('#superadmin_business_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'index']) }}",
            data: function (d) {
                d.package_id = $('#package_id').val();
                d.subscription_status = $('#subscription_status').val();
                d.is_active = $('#is_active').val();
                d.last_transaction_date = $('#last_transaction_date').val();
                d.no_transaction_since = $('#no_transaction_since').val();
            },
        },
        aaSorting: [[0, 'desc']],
        columns: [
            { data: 'created_at', name: 'business.created_at' },
            { data: 'name', name: 'business.name' },
            { data: 'owner_name', name: 'owner_name', searchable: false },
            { data: 'owner_email', name: 'u.email' },
            { data: 'contact_number', name: 'u.contact_number' },
            { data: 'business_contact_number', name: 'business_contact_number' },
            { data: 'address', name: 'address' },
            { data: 'is_active', name: 'is_active', searchable: false },
            { data: 'current_subscription', name: 'p.name' },
            { data: 'biz_creator', name: 'biz_creator', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    $('#package_id, #subscription_status, #is_active, #last_transaction_date, #no_transaction_since').change(function () {
        superadmin_business_table.ajax.reload();
    });

    $(document).on('click', 'a.delete_business_confirmation', function (e) {
        e.preventDefault();
        swal({
            title: LANG.sure,
            text: "Once deleted, you will not be able to recover this business!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then(function (confirmed) {
            if (confirmed) { window.location.href = $(e.target).closest('a').attr('href'); }
        });
    });
});
</script>
@endsection
