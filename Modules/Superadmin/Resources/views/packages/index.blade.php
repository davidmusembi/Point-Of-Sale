@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.packages'))

@section('content')
@include('superadmin::layouts.nav')
@include('superadmin::layouts.partials.currency')

<section class="content-header tw-px-4 sm:tw-px-6 tw-pb-0">
    <div class="tw-flex tw-items-center tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">@lang('superadmin::lang.packages')</h1>
            <p class="tw-text-sm tw-text-gray-500 tw-mt-0.5">@lang('superadmin::lang.all_packages')</p>
        </div>
        <a href="{{ action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'create']) }}"
           class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-rounded-lg tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-text-sm tw-font-semibold tw-shadow tw-transition-colors tw-no-underline">
            <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            @lang('messages.add')
        </a>
    </div>
</section>

<section class="content tw-px-4 sm:tw-px-6 tw-mt-5">
    @if($packages->isEmpty())
        <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm tw-p-12 tw-text-center">
            <svg class="tw-w-10 tw-h-10 tw-text-gray-300 tw-mx-auto tw-mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
            <p class="tw-text-sm tw-text-gray-500">No packages yet. <a href="{{ action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'create']) }}" class="tw-text-indigo-600 hover:tw-underline">Create your first package</a>.</p>
        </div>
    @else
    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-5">
        @foreach ($packages as $package)
        <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm tw-flex tw-flex-col tw-overflow-hidden">

            {{-- Header --}}
            <div class="tw-px-5 tw-pt-5 tw-pb-4 tw-border-b tw-border-gray-100">
                <div class="tw-flex tw-items-start tw-justify-between tw-gap-2">
                    <div>
                        <h3 class="tw-text-base tw-font-bold tw-text-gray-900 tw-leading-tight">{{ $package->name }}</h3>
                        <p class="tw-text-xs tw-text-gray-500 tw-mt-0.5">{{ $package->description }}</p>
                    </div>
                    <div class="tw-flex tw-flex-col tw-items-end tw-gap-1 tw-shrink-0">
                        @if ($package->is_active)
                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-50 tw-text-green-700">@lang('superadmin::lang.active')</span>
                        @else
                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-50 tw-text-red-700">@lang('superadmin::lang.inactive')</span>
                        @endif
                        @if ($package->mark_package_as_popular)
                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-amber-50 tw-text-amber-700">@lang('superadmin::lang.popular')</span>
                        @endif
                    </div>
                </div>

                {{-- Price --}}
                <div class="tw-mt-3">
                    @if ($package->price != 0)
                        <span class="tw-text-2xl tw-font-bold tw-text-gray-900">
                            <span class="display_currency" data-currency_symbol="true">{{ $package->price }}</span>
                        </span>
                        <span class="tw-text-sm tw-text-gray-500 tw-ml-1">/ {{ $package->interval_count }} {{ __('lang_v1.' . $package->interval) }}</span>
                    @else
                        <span class="tw-text-lg tw-font-semibold tw-text-green-600">
                            @lang('superadmin::lang.free_for_duration', ['duration' => $package->interval_count . ' ' . __('lang_v1.' . $package->interval)])
                        </span>
                    @endif
                </div>
            </div>

            {{-- Limits --}}
            <div class="tw-px-5 tw-py-4 tw-flex-1">
                <div class="tw-grid tw-grid-cols-2 tw-gap-2 tw-text-xs">
                    @foreach ([
                        [__('superadmin::lang.location_count'), $package->location_count],
                        [__('superadmin::lang.user_count'), $package->user_count],
                        [__('superadmin::lang.product_count'), $package->product_count],
                        [__('superadmin::lang.invoice_count'), $package->invoice_count],
                    ] as [$label, $count])
                    <div class="tw-flex tw-items-center tw-gap-1.5">
                        <svg class="tw-w-3.5 tw-h-3.5 tw-text-indigo-400 tw-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="tw-text-gray-600">
                            @if($count == 0) <span class="tw-font-medium tw-text-indigo-600">@lang('superadmin::lang.unlimited')</span>
                            @else <span class="tw-font-medium tw-text-gray-900">{{ $count }}</span>
                            @endif
                            {{ $label }}
                        </span>
                    </div>
                    @endforeach

                    @if ($package->trial_days)
                    <div class="tw-flex tw-items-center tw-gap-1.5 tw-col-span-2">
                        <svg class="tw-w-3.5 tw-h-3.5 tw-text-indigo-400 tw-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="tw-text-gray-600"><span class="tw-font-medium tw-text-gray-900">{{ $package->trial_days }}</span> @lang('superadmin::lang.trial_days')</span>
                    </div>
                    @endif
                </div>

                {{-- Custom Permissions --}}
                @if (!empty($package->custom_permissions))
                    <div class="tw-mt-3 tw-flex tw-flex-wrap tw-gap-1">
                        @foreach ($package->custom_permissions as $perm => $val)
                            @isset($permission_formatted[$perm])
                                <span class="tw-inline-flex tw-px-2 tw-py-0.5 tw-rounded tw-bg-indigo-50 tw-text-indigo-700 tw-text-xs">{{ $permission_formatted[$perm] }}</span>
                            @endisset
                        @endforeach
                    </div>
                @endif

                {{-- Flags --}}
                <div class="tw-mt-3 tw-flex tw-flex-wrap tw-gap-1.5">
                    @if ($package->is_private)
                        <span class="tw-inline-flex tw-items-center tw-gap-1 tw-px-2 tw-py-0.5 tw-rounded tw-bg-gray-100 tw-text-gray-600 tw-text-xs">
                            <svg class="tw-w-3 tw-h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            @lang('superadmin::lang.private_superadmin_only')
                        </span>
                    @endif
                    @if ($package->is_one_time)
                        <span class="tw-inline-flex tw-items-center tw-gap-1 tw-px-2 tw-py-0.5 tw-rounded tw-bg-sky-50 tw-text-sky-600 tw-text-xs">
                            @lang('superadmin::lang.one_time_only_subscription')
                        </span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="tw-px-5 tw-py-3 tw-border-t tw-border-gray-100 tw-flex tw-items-center tw-justify-end tw-gap-2">
                <a href="{{ action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'edit'], [$package->id]) }}"
                   class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-xs tw-font-medium tw-text-indigo-600 hover:tw-bg-indigo-50 tw-transition-colors tw-no-underline">
                    <svg class="tw-w-3.5 tw-h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    @lang('messages.edit')
                </a>
                <a href="{{ action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'destroy'], [$package->id]) }}"
                   class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-xs tw-font-medium tw-text-red-600 hover:tw-bg-red-50 tw-transition-colors tw-no-underline link_confirmation">
                    <svg class="tw-w-3.5 tw-h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    @lang('messages.delete')
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="tw-mt-5">
        {{ $packages->links() }}
    </div>
    @endif

    <div class="modal fade brands_modal" tabindex="-1" role="dialog"></div>
</section>
@endsection
