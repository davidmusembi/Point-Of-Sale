@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | Dashboard')

@section('content')
@include('superadmin::layouts.nav')

@include('superadmin::layouts.partials.currency')

<section class="content-header tw-px-4 sm:tw-px-6 tw-pb-0">
    <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-3">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">@lang('superadmin::lang.welcome_superadmin')</h1>
            <p class="tw-text-sm tw-text-gray-500 tw-mt-0.5">{{ date('l, F j Y') }}</p>
        </div>

        {{-- Date filter tabs --}}
        <div class="tw-inline-flex tw-rounded-lg tw-border tw-border-gray-200 tw-bg-gray-50 tw-p-1 tw-gap-0.5" data-toggle="buttons">
            @foreach([
                ['label' => __('home.today'),              'start' => date('Y-m-d'),                          'end' => date('Y-m-d'),                          'checked' => true],
                ['label' => __('home.this_week'),          'start' => $date_filters['this_week']['start'],     'end' => $date_filters['this_week']['end'],       'checked' => false],
                ['label' => __('home.this_month'),         'start' => $date_filters['this_month']['start'],    'end' => $date_filters['this_month']['end'],      'checked' => false],
                ['label' => __('superadmin::lang.this_year'), 'start' => $date_filters['this_yr']['start'],   'end' => $date_filters['this_yr']['end'],          'checked' => false],
            ] as $f)
                <label class="tw-cursor-pointer tw-px-3 tw-py-1.5 tw-rounded-md tw-text-xs tw-font-medium tw-transition-colors tw-mb-0
                              {{ $f['checked'] ? 'tw-bg-white tw-text-gray-900 tw-shadow-sm' : 'tw-text-gray-500 hover:tw-text-gray-700' }}">
                    <input type="radio" name="date-filter"
                           data-start="{{ $f['start'] }}" data-end="{{ $f['end'] }}"
                           class="tw-sr-only" @if($f['checked']) checked @endif>
                    {{ $f['label'] }}
                </label>
            @endforeach
        </div>
    </div>
</section>

<section class="content tw-px-4 sm:tw-px-6 tw-mt-5">

    {{-- ── Stat Cards ── --}}
    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-4 tw-mb-6">

        {{-- New Subscriptions --}}
        <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm tw-p-5">
            <div class="tw-flex tw-items-start tw-justify-between">
                <div>
                    <p class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-mb-1">@lang('superadmin::lang.new_subscriptions')</p>
                    <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><span class="new_subscriptions"><i class="fa fa-refresh fa-spin"></i></span></p>
                </div>
                <span class="tw-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-xl tw-bg-indigo-50 tw-text-indigo-600 tw-shrink-0">
                    <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 11a8.1 8.1 0 0 0-15.5-2m-.5-4v4h4M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/>
                    </svg>
                </span>
            </div>
            <a href="{{ action([\Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController::class, 'index']) }}"
               class="tw-inline-flex tw-items-center tw-gap-1 tw-mt-3 tw-text-xs tw-font-medium tw-text-indigo-600 hover:tw-text-indigo-700 tw-no-underline">
                @lang('superadmin::lang.more_info') <svg class="tw-w-3 tw-h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        {{-- New Registrations --}}
        <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm tw-p-5">
            <div class="tw-flex tw-items-start tw-justify-between">
                <div>
                    <p class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-mb-1">@lang('superadmin::lang.new_registrations')</p>
                    <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><span class="new_registrations"><i class="fa fa-refresh fa-spin"></i></span></p>
                </div>
                <span class="tw-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-xl tw-bg-green-50 tw-text-green-600 tw-shrink-0">
                    <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    </svg>
                </span>
            </div>
            <a href="{{ action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'index']) }}"
               class="tw-inline-flex tw-items-center tw-gap-1 tw-mt-3 tw-text-xs tw-font-medium tw-text-green-600 hover:tw-text-green-700 tw-no-underline">
                @lang('superadmin::lang.more_info') <svg class="tw-w-3 tw-h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        {{-- Not Subscribed --}}
        <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm tw-p-5">
            <div class="tw-flex tw-items-start tw-justify-between">
                <div>
                    <p class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-mb-1">@lang('superadmin::lang.not_subscribed')</p>
                    <p class="tw-text-3xl tw-font-bold tw-text-gray-900">{{ $not_subscribed }}</p>
                </div>
                <span class="tw-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-xl tw-bg-amber-50 tw-text-amber-600 tw-shrink-0">
                    <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
            </div>
            <a href="{{ action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'index']) }}"
               class="tw-inline-flex tw-items-center tw-gap-1 tw-mt-3 tw-text-xs tw-font-medium tw-text-amber-600 hover:tw-text-amber-700 tw-no-underline">
                @lang('superadmin::lang.more_info') <svg class="tw-w-3 tw-h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

    </div>

    {{-- ── Monthly Trend Chart ── --}}
    <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
        <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
            <svg class="tw-w-5 tw-h-5 tw-text-sky-500 tw-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
            </svg>
            <h2 class="tw-text-sm tw-font-semibold tw-text-gray-800">{{ __('superadmin::lang.monthly_sales_trend') }}</h2>
        </div>
        <div class="tw-p-5">
            {!! $monthly_sells_chart->container() !!}
        </div>
    </div>

</section>
@endsection

@section('javascript')
{!! $monthly_sells_chart->script() !!}
<script>
$(document).ready(function () {
    var start = $('input[name="date-filter"]:checked').data('start');
    var end   = $('input[name="date-filter"]:checked').data('end');
    updateStats(start, end);

    $(document).on('change', 'input[name="date-filter"]', function () {
        var label = $(this).closest('label');
        label.closest('[data-toggle]').find('label').removeClass('tw-bg-white tw-text-gray-900 tw-shadow-sm').addClass('tw-text-gray-500');
        label.addClass('tw-bg-white tw-text-gray-900 tw-shadow-sm').removeClass('tw-text-gray-500');
        updateStats($(this).data('start'), $(this).data('end'));
    });
});

function updateStats(start, end) {
    var spin = '<i class="fa fa-refresh fa-spin"></i>';
    $('.new_subscriptions, .new_registrations').html(spin);
    $.get('/superadmin/stats', { start: start, end: end }, function (data) {
        $('.new_subscriptions').html(__currency_trans_from_en(data.new_subscriptions, true, true));
        $('.new_registrations').html(data.new_registrations);
    }, 'json');
}
</script>
@endsection
