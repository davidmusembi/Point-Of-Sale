@extends('layouts.app')
@section('title', 'Superadmin | ' . __('superadmin::lang.subscription'))

@section('content')
@include('superadmin::layouts.nav')
@include('superadmin::layouts.partials.currency')

<section class="content-header tw-px-4 sm:tw-px-6 tw-pb-0">
    <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">@lang('superadmin::lang.subscription')</h1>
    <p class="tw-text-sm tw-text-gray-500 tw-mt-0.5">@lang('superadmin::lang.view_subscription')</p>
</section>

<section class="content tw-px-4 sm:tw-px-6 tw-mt-5">

    {{-- Filters --}}
    <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm tw-mb-5">
        <div class="tw-px-5 tw-py-3 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-2 tw-cursor-pointer"
             data-toggle="collapse" data-target="#subscriptionFilters">
            <svg class="tw-w-4 tw-h-4 tw-text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <span class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">@lang('report.filters')</span>
        </div>
        <div id="subscriptionFilters" class="collapse in">
            <div class="tw-p-5 tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-4">
                <div>
                    <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.packages')</label>
                    {!! Form::select('package_id', $packages, null, ['class' => 'form-control input-sm select2', 'placeholder' => __('lang_v1.all'), 'id' => 'package_id']) !!}
                </div>
                <div>
                    <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.status')</label>
                    {!! Form::select('subscription_status', $subscription_statuses, null, ['class' => 'form-control input-sm select2', 'placeholder' => __('lang_v1.all'), 'id' => 'subscription_status']) !!}
                </div>
                <div>
                    <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('lang_v1.created_at')</label>
                    {!! Form::text('created_at', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control input-sm', 'readonly', 'id' => 'created_at']) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
        <div class="tw-p-5">
            @can('superadmin')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="superadmin_subscription_table">
                    <thead>
                        <tr>
                            <th>@lang('superadmin::lang.business_name')</th>
                            <th>@lang('superadmin::lang.package_name')</th>
                            <th>@lang('superadmin::lang.status')</th>
                            <th>@lang('lang_v1.created_at')</th>
                            <th>@lang('superadmin::lang.start_date')</th>
                            <th>@lang('superadmin::lang.trial_end_date')</th>
                            <th>@lang('superadmin::lang.end_date')</th>
                            <th>@lang('superadmin::lang.coupon_code')</th>
                            <th>@lang('superadmin::lang.original_price')</th>
                            <th>@lang('superadmin::lang.paid_amount')</th>
                            <th>@lang('superadmin::lang.paid_via')</th>
                            <th>@lang('superadmin::lang.payment_transaction_id')</th>
                            <th>@lang('superadmin::lang.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
            @endcan
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog"></div>

</section>
@endsection

@section('javascript')
<script>
$(document).ready(function () {
    $('.select2').select2();

    $('#created_at').daterangepicker(dateRangeSettings, function (start, end) {
        $('#created_at').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
        superadmin_subscription_table.ajax.reload();
    });
    $('#created_at').on('cancel.daterangepicker', function () {
        $('#created_at').val('');
        superadmin_subscription_table.ajax.reload();
    });

    $('#package_id, #subscription_status').change(function () {
        superadmin_subscription_table.ajax.reload();
    });

    var superadmin_subscription_table = $('#superadmin_subscription_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/superadmin/superadmin-subscription',
            data: function (d) {
                d.package_id = $('#package_id').val();
                d.status = $('#subscription_status').val();
                var start = '', end = '';
                if ($('#created_at').val()) {
                    start = $('input#created_at').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    end   = $('input#created_at').data('daterangepicker').endDate.format('YYYY-MM-DD');
                }
                d.start_date = start;
                d.end_date = end;
                d = __datatable_ajax_callback(d);
            },
        },
        columnDefs: [{ targets: 12, orderable: false, searchable: false }],
        fnDrawCallback: function () {
            __currency_convert_recursively($('#superadmin_subscription_table'), true);
        }
    });

    $(document).on('click', 'button.change_status', function () {
        $('div#statusModal').load($(this).data('href'), function () {
            $(this).modal('show');
            $('form#status_change_form').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    method: 'POST', dataType: 'json',
                    data: $(this).serialize(), url: $(this).attr('action'),
                    success: function (result) {
                        if (result.success) {
                            $('div#statusModal').modal('hide');
                            toastr.success(result.msg);
                            superadmin_subscription_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });
        });
    });

    $(document).on('shown.bs.modal', '.view_modal', function () {
        $('.edit-subscription-modal .datepicker').datepicker({ autoclose: true, format: datepicker_date_format });
        $('form#edit_subscription_form').submit(function (e) {
            e.preventDefault();
            $.ajax({
                method: 'POST', dataType: 'json',
                data: $(this).serialize(), url: $(this).attr('action'),
                success: function (result) {
                    if (result.success) {
                        $('div.view_modal').modal('hide');
                        toastr.success(result.msg);
                        superadmin_subscription_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });
    });
});
</script>
@endsection
