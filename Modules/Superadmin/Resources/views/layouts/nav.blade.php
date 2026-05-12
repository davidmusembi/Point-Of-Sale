@php
    $seg2 = request()->segment(2);
    $navLinks = [
        ['label' => __('superadmin::lang.all_business'),         'seg2' => 'business',               'url' => action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'index'])],
        ['label' => __('superadmin::lang.subscription'),          'seg2' => 'superadmin-subscription', 'url' => action([\Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController::class, 'index'])],
        ['label' => __('superadmin::lang.subscription_packages'), 'seg2' => 'packages',               'url' => action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'index'])],
        ['label' => __('superadmin::lang.all_coupons'),           'seg2' => 'coupons',                'url' => action([\Modules\Superadmin\Http\Controllers\CouponController::class, 'index'])],
        ['label' => __('superadmin::lang.communicator'),          'seg2' => 'communicator',           'url' => action([\Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'index'])],
        ['label' => 'Tenants',                                    'seg2' => 'tenants',                'url' => route('superadmin.tenants.index')],
        ['label' => __('superadmin::lang.super_admin_settings'),  'seg2' => 'settings',               'url' => action([\Modules\Superadmin\Http\Controllers\SuperadminSettingsController::class, 'edit'])],
    ];
@endphp

<header class="no-print tw-sticky tw-top-0 tw-z-30 tw-bg-white tw-border-b tw-border-gray-200 tw-shadow-sm">
    <div class="tw-px-4 sm:tw-px-6 tw-mx-auto">
        <div class="tw-flex tw-items-center tw-h-14 tw-gap-4">

            {{-- ── Brand ── --}}
            <a href="{{ action([\Modules\Superadmin\Http\Controllers\SuperadminController::class, 'index']) }}"
               class="tw-flex tw-items-center tw-gap-2.5 tw-shrink-0 tw-text-indigo-700 tw-font-bold tw-text-sm tw-no-underline">
                <span class="tw-flex tw-items-center tw-justify-center tw-w-7 tw-h-7 tw-rounded-lg tw-bg-indigo-600 tw-text-white tw-shrink-0">
                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <span class="tw-hidden sm:tw-block tw-leading-tight">
                    {{ config('app.name', 'ultimatePOS') }}<br>
                    <span class="tw-text-xs tw-font-normal tw-text-gray-400 tw-tracking-wide">SuperAdmin Panel</span>
                </span>
            </a>

            {{-- ── Desktop nav ── --}}
            <nav class="tw-hidden lg:tw-flex tw-items-center tw-gap-0.5 tw-flex-1 tw-ml-4 tw-overflow-x-auto">
                @foreach($navLinks as $link)
                    <a href="{{ $link['url'] }}"
                       class="tw-whitespace-nowrap tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-colors tw-no-underline
                              {{ $seg2 === $link['seg2']
                                 ? 'tw-bg-indigo-50 tw-text-indigo-700'
                                 : 'tw-text-gray-600 hover:tw-bg-gray-100 hover:tw-text-gray-900' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- ── Right side ── --}}
            <div class="tw-flex tw-items-center tw-gap-2 tw-ml-auto tw-shrink-0">
                {{-- User badge --}}
                <div class="tw-hidden sm:tw-flex tw-items-center tw-gap-1.5 tw-px-2.5 tw-py-1 tw-rounded-lg tw-bg-gray-50 tw-border tw-border-gray-200">
                    <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-5 tw-h-5 tw-rounded-full tw-bg-indigo-100 tw-text-indigo-600 tw-text-xs tw-font-bold tw-uppercase tw-shrink-0">
                        {{ substr(auth()->user()->first_name ?? auth()->user()->username, 0, 1) }}
                    </span>
                    <span class="tw-text-sm tw-text-gray-700 tw-font-medium tw-leading-none">
                        {{ auth()->user()->first_name ?? auth()->user()->username }}
                    </span>
                </div>

                {{-- Logout --}}
                <form id="sa-logout-form" action="{{ url('/logout') }}" method="POST" class="tw-inline">
                    @csrf
                    <button type="submit"
                            class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-sm tw-font-medium tw-text-red-600 hover:tw-bg-red-50 tw-border tw-border-transparent hover:tw-border-red-100 tw-transition-colors tw-bg-transparent tw-cursor-pointer"
                            onclick="return confirm('Log out?')">
                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="tw-hidden sm:tw-inline">Logout</span>
                    </button>
                </form>

                {{-- Mobile hamburger --}}
                <button id="sa-mobile-btn" type="button"
                        class="lg:tw-hidden tw-p-1.5 tw-rounded-lg tw-text-gray-500 hover:tw-bg-gray-100 tw-border-0 tw-bg-transparent tw-cursor-pointer">
                    <svg class="tw-w-5 tw-h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── Mobile nav dropdown ── --}}
        <div id="sa-mobile-menu" class="lg:tw-hidden tw-hidden tw-border-t tw-border-gray-100 tw-py-2">
            @foreach($navLinks as $link)
                <a href="{{ $link['url'] }}"
                   class="tw-flex tw-px-3 tw-py-2.5 tw-rounded-lg tw-text-sm tw-font-medium tw-mb-0.5 tw-no-underline
                          {{ $seg2 === $link['seg2']
                             ? 'tw-bg-indigo-50 tw-text-indigo-700'
                             : 'tw-text-gray-600 hover:tw-bg-gray-100 hover:tw-text-gray-900' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</header>

<script>
    (function () {
        var btn = document.getElementById('sa-mobile-btn');
        var menu = document.getElementById('sa-mobile-menu');
        if (btn && menu) {
            btn.addEventListener('click', function () { menu.classList.toggle('tw-hidden'); });
        }
    })();
</script>
