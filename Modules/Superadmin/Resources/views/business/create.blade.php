@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.add_new_business'))

@section('content')
@include('superadmin::layouts.nav')

<section class="content-header">
    <div class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-500 tw-mb-1">
        <a href="{{ action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'index']) }}" class="hover:tw-underline">Businesses</a>
        <span>/</span>
        <span class="tw-font-medium tw-text-gray-700">New Business</span>
    </div>
    <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">@lang('superadmin::lang.add_new_business')</h1>
    <p class="tw-text-sm tw-text-gray-500 tw-mt-0.5">A welcome email with login credentials will be sent to the owner automatically.</p>
</section>

<section class="content">
    {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'store']), 'method' => 'post', 'id' => 'business_register_form', 'files' => true]) !!}

    <div class="tw-grid tw-grid-cols-1 xl:tw-grid-cols-3 tw-gap-5">

        {{-- ── Business Details ── --}}
        <div class="xl:tw-col-span-2 tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
            <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">Business Details</h2>
            </div>
            <div class="tw-p-5 tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4">

                <div class="sm:tw-col-span-2">
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                        @lang('business.business_name') <span class="tw-text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required
                           class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm tw-shadow-sm focus:tw-border-indigo-400 focus:tw-ring-1 focus:tw-ring-indigo-400 focus:tw-outline-none"
                           placeholder="e.g. Acme Store Ltd">
                </div>

                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">@lang('business.start_date')</label>
                    {!! Form::text('start_date', null, ['class' => 'form-control input-sm', 'id' => 'start_date', 'placeholder' => __('business.start_date')]) !!}
                </div>

                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">@lang('business.currency') <span class="tw-text-red-500">*</span></label>
                    {!! Form::select('currency_id', $currencies, null, ['class' => 'form-control input-sm select2', 'required']) !!}
                </div>

                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">@lang('business.time_zone') <span class="tw-text-red-500">*</span></label>
                    {!! Form::select('time_zone', $timezone_list, 'America/Chicago', ['class' => 'form-control input-sm select2', 'required']) !!}
                </div>

                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">@lang('business.accounting_method') <span class="tw-text-red-500">*</span></label>
                    {!! Form::select('accounting_method', $accounting_methods, 'fifo', ['class' => 'form-control input-sm', 'required']) !!}
                </div>

                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">@lang('business.fy_start_month') <span class="tw-text-red-500">*</span></label>
                    {!! Form::select('fy_start_month', $months, 1, ['class' => 'form-control input-sm', 'required']) !!}
                </div>

                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">@lang('business.logo')</label>
                    <input type="file" name="business_logo" id="business_logo" accept="image/*"
                           class="tw-block tw-w-full tw-text-sm tw-text-gray-500 file:tw-mr-3 file:tw-py-1.5 file:tw-px-3 file:tw-rounded-lg file:tw-border-0 file:tw-bg-indigo-50 file:tw-text-indigo-700 hover:file:tw-bg-indigo-100">
                </div>

                <div class="sm:tw-col-span-2">
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Subdomain</label>
                    <div class="tw-flex tw-rounded-lg tw-border tw-border-gray-300 tw-overflow-hidden tw-shadow-sm focus-within:tw-ring-1 focus-within:tw-ring-indigo-400 focus-within:tw-border-indigo-400">
                        <input type="text" name="subdomain" id="subdomain"
                               class="tw-flex-1 tw-px-3 tw-py-2 tw-text-sm tw-border-0 focus:tw-outline-none"
                               placeholder="my-business">
                        <span class="tw-inline-flex tw-items-center tw-px-3 tw-bg-gray-50 tw-text-gray-500 tw-text-sm tw-border-l tw-border-gray-300">.{{ env('APP_DOMAIN', 'localhost') }}</span>
                    </div>
                    <p class="tw-text-xs tw-text-gray-400 tw-mt-1">Auto-generated from business name.</p>
                </div>

            </div>
        </div>

        {{-- ── Right column ── --}}
        <div class="tw-flex tw-flex-col tw-gap-5">

            {{-- Owner --}}
            <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
                <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                    <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">Owner Account</h2>
                </div>
                <div class="tw-p-5 tw-flex tw-flex-col tw-gap-4">

                    <div class="tw-grid tw-grid-cols-3 tw-gap-3">
                        <div>
                            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">Prefix</label>
                            {!! Form::select('surname', ['' => '', 'Mr' => 'Mr', 'Mrs' => 'Mrs', 'Ms' => 'Ms', 'Dr' => 'Dr'], null, ['class' => 'form-control input-sm']) !!}
                        </div>
                        <div class="tw-col-span-2">
                            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('business.first_name') <span class="tw-text-red-500">*</span></label>
                            <input type="text" name="first_name" id="first_name" required
                                   class="form-control input-sm" placeholder="{{ __('business.first_name') }}">
                        </div>
                    </div>

                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('business.last_name')</label>
                        <input type="text" name="last_name" class="form-control input-sm"
                               placeholder="{{ __('business.last_name') }}">
                    </div>

                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('business.username') <span class="tw-text-red-500">*</span></label>
                        <input type="text" name="username" id="username" required minlength="4"
                               class="form-control input-sm" placeholder="{{ __('business.username') }}">
                    </div>

                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('business.email') <span class="tw-text-red-500">*</span></label>
                        <input type="email" name="email" id="email" required
                               class="form-control input-sm" placeholder="{{ __('business.email') }}">
                        <p class="tw-text-xs tw-text-indigo-600 tw-mt-1 tw-flex tw-items-center tw-gap-1">
                            <svg class="tw-w-3.5 tw-h-3.5 tw-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Credentials sent here automatically.
                        </p>
                    </div>

                </div>
            </div>

            {{-- Subscription --}}
            <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
                <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                    <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        Subscription <span class="tw-normal-case tw-font-normal tw-text-gray-400">(optional)</span>
                    </h2>
                </div>
                <div class="tw-p-5 tw-flex tw-flex-col tw-gap-4">
                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.subscription_packages')</label>
                        {!! Form::select('package_id', $packages, null, ['class' => 'form-control input-sm', 'placeholder' => __('messages.please_select'), 'id' => 'package_id']) !!}
                    </div>
                    <div id="subscription_extra" class="tw-flex tw-flex-col tw-gap-4 tw-hidden">
                        <div>
                            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.paid_via') <span class="tw-text-red-500">*</span></label>
                            {!! Form::select('paid_via', $gateways, null, ['class' => 'form-control input-sm', 'placeholder' => __('messages.please_select')]) !!}
                        </div>
                        <div>
                            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.payment_transaction_id')</label>
                            <input type="text" name="payment_transaction_id" class="form-control input-sm"
                                   placeholder="{{ __('superadmin::lang.payment_transaction_id') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="tw-w-full tw-flex tw-items-center tw-justify-center tw-gap-2 tw-py-3 tw-rounded-xl tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-semibold tw-text-sm tw-shadow tw-transition-colors">
                <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Create Business &amp; Send Credentials
            </button>
        </div>

    </div>
    {!! Form::close() !!}
</section>
@endsection

@section('javascript')
<script>
$(document).ready(function () {
    $('.select2').select2();
    $('#start_date').datepicker({ dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true });

    $('#package_id').on('change', function () {
        $('#subscription_extra').toggleClass('tw-hidden', !$(this).val());
    });

    var subdomainTouched = false;
    $('#name').on('input', function () {
        if (!subdomainTouched) {
            $('#subdomain').val($(this).val()
                .toLowerCase().replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-+|-+$/g, ''));
        }
    });
    $('#subdomain').on('input', function () { subdomainTouched = true; });

    $('form#business_register_form').validate({
        errorPlacement: function (error, element) {
            error.insertAfter(element.parent('.input-group').length ? element.parent() : element);
        },
        rules: {
            name: 'required',
            first_name: 'required',
            username: {
                required: true, minlength: 4,
                remote: { url: '/business/register/check-username', type: 'post',
                          data: { username: function () { return $('#username').val(); } } }
            },
            email: {
                required: true, email: true,
                remote: { url: '/business/register/check-email', type: 'post',
                          data: { email: function () { return $('#email').val(); } } }
            },
            paid_via: { required: function () { return !!$('#package_id').val(); } }
        },
        messages: {
            username: { remote: LANG.invalid_username },
            email: { remote: '{{ __("validation.unique", ["attribute" => __("business.email")]) }}' }
        }
    });
});
</script>
@endsection
