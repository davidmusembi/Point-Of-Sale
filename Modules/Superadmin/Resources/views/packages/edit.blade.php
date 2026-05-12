@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.packages'))

@section('content')
@include('superadmin::layouts.nav')

<section class="content-header tw-px-4 sm:tw-px-6 tw-pb-0">
    <div class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-500 tw-mb-1">
        <a href="{{ action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'index']) }}" class="hover:tw-underline">@lang('superadmin::lang.packages')</a>
        <span>/</span>
        <span class="tw-font-medium tw-text-gray-700">@lang('superadmin::lang.edit_package')</span>
    </div>
    <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">@lang('superadmin::lang.edit_package'): {{ $packages->name }}</h1>
</section>

<section class="content tw-px-4 sm:tw-px-6 tw-mt-5">
    {!! Form::open(['route' => ['packages.update', $packages->id], 'method' => 'put', 'id' => 'edit_package_form']) !!}

    <div class="tw-grid tw-grid-cols-1 xl:tw-grid-cols-3 tw-gap-5">

        {{-- ── Left column ── --}}
        <div class="xl:tw-col-span-2 tw-flex tw-flex-col tw-gap-5">

            {{-- Basic Details --}}
            <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
                <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                    <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">Package Details</h2>
                </div>
                <div class="tw-p-5 tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4">
                    <div class="sm:tw-col-span-2">
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">@lang('lang_v1.name') <span class="tw-text-red-500">*</span></label>
                        {!! Form::text('name', $packages->name, ['class' => 'tw-block tw-w-full tw-rounded-lg tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm tw-shadow-sm focus:tw-border-indigo-400 focus:tw-ring-1 focus:tw-ring-indigo-400 focus:tw-outline-none', 'required']) !!}
                    </div>
                    <div class="sm:tw-col-span-2">
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">@lang('superadmin::lang.description') <span class="tw-text-red-500">*</span></label>
                        {!! Form::text('description', $packages->description, ['class' => 'tw-block tw-w-full tw-rounded-lg tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm tw-shadow-sm focus:tw-border-indigo-400 focus:tw-ring-1 focus:tw-ring-indigo-400 focus:tw-outline-none', 'required']) !!}
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">@lang('superadmin::lang.sort_order') <span class="tw-text-red-500">*</span></label>
                        {!! Form::number('sort_order', $packages->sort_order, ['class' => 'form-control input-sm', 'required']) !!}
                    </div>
                </div>
            </div>

            {{-- Resource Limits --}}
            <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
                <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                    <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        Resource Limits
                        <span class="tw-normal-case tw-font-normal tw-text-gray-400 tw-ml-1">(0 = @lang('superadmin::lang.unlimited'))</span>
                    </h2>
                </div>
                <div class="tw-p-5 tw-grid tw-grid-cols-2 sm:tw-grid-cols-4 tw-gap-4">
                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.location_count') <span class="tw-text-red-500">*</span></label>
                        {!! Form::number('location_count', $packages->location_count, ['class' => 'form-control input-sm', 'required', 'min' => 0]) !!}
                    </div>
                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.user_count') <span class="tw-text-red-500">*</span></label>
                        {!! Form::number('user_count', $packages->user_count, ['class' => 'form-control input-sm', 'required', 'min' => 0]) !!}
                    </div>
                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.product_count') <span class="tw-text-red-500">*</span></label>
                        {!! Form::number('product_count', $packages->product_count, ['class' => 'form-control input-sm', 'required', 'min' => 0]) !!}
                    </div>
                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.invoice_count') <span class="tw-text-red-500">*</span></label>
                        {!! Form::number('invoice_count', $packages->invoice_count, ['class' => 'form-control input-sm', 'required', 'min' => 0]) !!}
                    </div>
                </div>
            </div>

            {{-- Module Permissions --}}
            @if (!empty($permissions))
            <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
                <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                    <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">Module Permissions</h2>
                </div>
                <div class="tw-p-5 tw-space-y-5">
                    @foreach ($permissions as $module => $module_permissions)
                        <div>
                            <p class="tw-text-xs tw-font-semibold tw-text-indigo-600 tw-uppercase tw-tracking-wider tw-mb-3">{{ $module }}</p>
                            <div class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-3 lg:tw-grid-cols-4 tw-gap-3">
                                @foreach ($module_permissions as $permission)
                                    @php
                                        $value = isset($packages->custom_permissions[$permission['name']])
                                            ? $packages->custom_permissions[$permission['name']]
                                            : false;
                                    @endphp
                                    @if (isset($permission['field_type']) && in_array($permission['field_type'], ['number', 'input']))
                                        <div>
                                            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">
                                                {{ $permission['label'] }}
                                                @if (isset($permission['tooltip'])) @show_tooltip($permission['tooltip']) @endif
                                            </label>
                                            {!! Form::text("custom_permissions[$permission[name]]", $value, ['class' => 'form-control input-sm', 'type' => $permission['field_type']]) !!}
                                        </div>
                                    @else
                                        <label class="tw-flex tw-items-center tw-gap-2 tw-cursor-pointer">
                                            {!! Form::checkbox("custom_permissions[$permission[name]]", 1, $value, ['class' => 'input-icheck']) !!}
                                            <span class="tw-text-sm tw-text-gray-700">{{ $permission['label'] }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- ── Right column ── --}}
        <div class="tw-flex tw-flex-col tw-gap-5">

            {{-- Billing --}}
            <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
                <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                    <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">Billing</h2>
                </div>
                <div class="tw-p-5 tw-flex tw-flex-col tw-gap-4">
                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.interval') <span class="tw-text-red-500">*</span></label>
                        {!! Form::select('interval', $intervals, $packages->interval, ['class' => 'form-control input-sm select2', 'placeholder' => __('messages.please_select'), 'required']) !!}
                    </div>
                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.interval_count') <span class="tw-text-red-500">*</span></label>
                        {!! Form::number('interval_count', $packages->interval_count, ['class' => 'form-control input-sm', 'required', 'min' => 1]) !!}
                    </div>
                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.trial_days') <span class="tw-text-red-500">*</span></label>
                        {!! Form::number('trial_days', $packages->trial_days, ['class' => 'form-control input-sm', 'required', 'min' => 0]) !!}
                    </div>
                    <div>
                        <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.price') <span class="tw-text-red-500">*</span></label>
                        {!! Form::text('price', $packages->price, ['class' => 'form-control input-sm input_number', 'required']) !!}
                    </div>
                </div>
            </div>

            {{-- Options --}}
            <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
                <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                    <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">Options</h2>
                </div>
                <div class="tw-p-5 tw-flex tw-flex-col tw-gap-3">
                    <label class="tw-flex tw-items-center tw-gap-2.5 tw-cursor-pointer">
                        {!! Form::checkbox('is_active', 1, $packages->is_active, ['class' => 'input-icheck']) !!}
                        <span class="tw-text-sm tw-text-gray-700">@lang('superadmin::lang.is_active')</span>
                    </label>
                    <label class="tw-flex tw-items-center tw-gap-2.5 tw-cursor-pointer">
                        {!! Form::checkbox('is_private', 1, $packages->is_private, ['class' => 'input-icheck']) !!}
                        <span class="tw-text-sm tw-text-gray-700">@lang('superadmin::lang.private_superadmin_only')</span>
                    </label>
                    <label class="tw-flex tw-items-center tw-gap-2.5 tw-cursor-pointer">
                        {!! Form::checkbox('is_one_time', 1, $packages->is_one_time, ['class' => 'input-icheck']) !!}
                        <span class="tw-text-sm tw-text-gray-700">@lang('superadmin::lang.one_time_only_subscription')</span>
                    </label>
                    <label class="tw-flex tw-items-center tw-gap-2.5 tw-cursor-pointer">
                        {!! Form::checkbox('mark_package_as_popular', 1, $packages->mark_package_as_popular, ['class' => 'input-icheck']) !!}
                        <span class="tw-text-sm tw-text-gray-700">@lang('superadmin::lang.mark_package_as_popular')</span>
                    </label>
                    <label class="tw-flex tw-items-center tw-gap-2.5 tw-cursor-pointer">
                        {!! Form::checkbox('enable_custom_link', 1, $packages->enable_custom_link, ['class' => 'input-icheck', 'id' => 'enable_custom_link']) !!}
                        <span class="tw-text-sm tw-text-gray-700">@lang('superadmin::lang.enable_custom_subscription_link')</span>
                    </label>
                    <div id="custom_link_div" @if(empty($packages->enable_custom_link)) class="tw-hidden tw-flex tw-flex-col tw-gap-3 tw-pl-6 tw-border-l-2 tw-border-indigo-100" @else class="tw-flex tw-flex-col tw-gap-3 tw-pl-6 tw-border-l-2 tw-border-indigo-100" @endif>
                        <div>
                            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.custom_link')</label>
                            {!! Form::text('custom_link', $packages->custom_link, ['class' => 'form-control input-sm']) !!}
                        </div>
                        <div>
                            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1">@lang('superadmin::lang.custom_link_text')</label>
                            {!! Form::text('custom_link_text', $packages->custom_link_text, ['class' => 'form-control input-sm']) !!}
                        </div>
                    </div>
                    <div class="tw-pt-2 tw-border-t tw-border-gray-100">
                        <label class="tw-flex tw-items-center tw-gap-2.5 tw-cursor-pointer">
                            {!! Form::checkbox('update_subscriptions', 1, false, ['class' => 'input-icheck']) !!}
                            <span class="tw-text-sm tw-text-gray-700">
                                @lang('superadmin::lang.update_existing_subscriptions')
                                @show_tooltip(__('superadmin::lang.update_existing_subscriptions_tooltip'))
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Restrict to Businesses --}}
            <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
                <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                    <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        @lang('superadmin::lang.only_for_businesses') @show_tooltip(__('superadmin::lang.tooltip_only_for_businesses'))
                    </h2>
                </div>
                <div class="tw-p-5">
                    {!! Form::select('businesses[]', $businesses, json_decode($packages->businesses), ['class' => 'form-control input-sm select2', 'multiple']) !!}
                    <p class="tw-text-xs tw-text-gray-400 tw-mt-1">Leave empty for all businesses.</p>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="tw-w-full tw-flex tw-items-center tw-justify-center tw-gap-2 tw-py-3 tw-rounded-xl tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-font-semibold tw-text-sm tw-shadow tw-transition-colors">
                <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                @lang('messages.save')
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
    $('form#edit_package_form').validate();
    $('#enable_custom_link').on('ifChecked', function () { $('#custom_link_div').removeClass('tw-hidden'); });
    $('#enable_custom_link').on('ifUnchecked', function () { $('#custom_link_div').addClass('tw-hidden'); });
});
</script>
@endsection
