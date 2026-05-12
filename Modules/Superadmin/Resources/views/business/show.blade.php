@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . $business->name)

@section('content')
@include('superadmin::layouts.nav')

<section class="content-header tw-px-4 sm:tw-px-6 tw-pb-0">
    <div class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-500 tw-mb-1">
        <a href="{{ action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'index']) }}" class="hover:tw-underline">@lang('superadmin::lang.all_business')</a>
        <span>/</span>
        <span class="tw-font-medium tw-text-gray-700">{{ $business->name }}</span>
    </div>
    <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">{{ $business->name }}</h1>
</section>

<section class="content tw-px-4 sm:tw-px-6 tw-mt-5 tw-flex tw-flex-col tw-gap-5">

    {{-- ── Overview cards ── --}}
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-5">

        {{-- Business Info --}}
        <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
            <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                <svg class="tw-w-4 tw-h-4 tw-text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">@lang('superadmin::lang.business_name')</h2>
            </div>
            <dl class="tw-p-5 tw-space-y-3 tw-text-sm">
                <div>
                    <dt class="tw-text-xs tw-font-medium tw-text-gray-500">@lang('business.business_name')</dt>
                    <dd class="tw-mt-0.5 tw-font-semibold tw-text-gray-900">{{ $business->name }}</dd>
                </div>
                <div>
                    <dt class="tw-text-xs tw-font-medium tw-text-gray-500">@lang('business.currency')</dt>
                    <dd class="tw-mt-0.5 tw-text-gray-700">{{ $business->currency->currency }}</dd>
                </div>
                @if (!empty($business->tax_number_1))
                <div>
                    <dt class="tw-text-xs tw-font-medium tw-text-gray-500">@lang('business.tax_number1')</dt>
                    <dd class="tw-mt-0.5 tw-text-gray-700">{{ $business->tax_label_1 }}: {{ $business->tax_number_1 }}</dd>
                </div>
                @endif
                @if (!empty($business->tax_number_2))
                <div>
                    <dt class="tw-text-xs tw-font-medium tw-text-gray-500">@lang('business.tax_number2')</dt>
                    <dd class="tw-mt-0.5 tw-text-gray-700">{{ $business->tax_label_2 }}: {{ $business->tax_number_2 }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Status & Meta --}}
        <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
            <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                <svg class="tw-w-4 tw-h-4 tw-text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">Status</h2>
            </div>
            <dl class="tw-p-5 tw-space-y-3 tw-text-sm">
                <div>
                    <dt class="tw-text-xs tw-font-medium tw-text-gray-500">@lang('business.is_active')</dt>
                    <dd class="tw-mt-0.5">
                        @if ($business->is_active)
                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-50 tw-text-green-700">Active</span>
                        @else
                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-red-50 tw-text-red-700">Inactive</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="tw-text-xs tw-font-medium tw-text-gray-500">@lang('business.time_zone')</dt>
                    <dd class="tw-mt-0.5 tw-text-gray-700">{{ $business->time_zone }}</dd>
                </div>
                @if (!empty($created_by))
                <div>
                    <dt class="tw-text-xs tw-font-medium tw-text-gray-500">@lang('business.created_by')</dt>
                    <dd class="tw-mt-0.5 tw-text-gray-700">{{ $created_by->surname }} {{ $created_by->first_name }} {{ $created_by->last_name }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Owner & Logo --}}
        <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
            <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
                <svg class="tw-w-4 tw-h-4 tw-text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                </svg>
                <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">@lang('business.owner')</h2>
            </div>
            <div class="tw-p-5">
                @if (!empty($business->logo))
                    <img src="{{ url('uploads/business_logos/' . $business->logo) }}" alt="Logo"
                         class="tw-w-16 tw-h-16 tw-rounded-lg tw-object-contain tw-border tw-border-gray-200 tw-mb-3">
                @endif
                @if (!empty($business->owner))
                <dl class="tw-space-y-2 tw-text-sm">
                    <div>
                        <dt class="tw-text-xs tw-font-medium tw-text-gray-500">Name</dt>
                        <dd class="tw-mt-0.5 tw-font-semibold tw-text-gray-900">{{ $business->owner->surname }} {{ $business->owner->first_name }} {{ $business->owner->last_name }}</dd>
                    </div>
                    <div>
                        <dt class="tw-text-xs tw-font-medium tw-text-gray-500">@lang('business.email')</dt>
                        <dd class="tw-mt-0.5 tw-text-gray-700">{{ $business->owner->email }}</dd>
                    </div>
                    @if ($business->owner->contact_no)
                    <div>
                        <dt class="tw-text-xs tw-font-medium tw-text-gray-500">@lang('business.mobile')</dt>
                        <dd class="tw-mt-0.5 tw-text-gray-700">{{ $business->owner->contact_no }}</dd>
                    </div>
                    @endif
                </dl>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Tenant & Database ── --}}
    @php $tenant = $business->tenant; @endphp
    <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
        <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-justify-between">
            <div class="tw-flex tw-items-center tw-gap-2">
                <svg class="tw-w-4 tw-h-4 tw-text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                </svg>
                <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">Tenant & Database</h2>
            </div>
            @if ($tenant)
            <div class="tw-flex tw-gap-2">
                <form method="POST" action="{{ route('superadmin.tenants.provision', $tenant->id) }}"
                      onsubmit="return confirm('Provision DB? This creates and migrates the tenant database.')">
                    @csrf
                    <button type="submit" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-xs tw-font-medium tw-text-teal-700 tw-border tw-border-teal-200 hover:tw-bg-teal-50 tw-transition-colors tw-bg-transparent tw-cursor-pointer">
                        <svg class="tw-w-3.5 tw-h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7"/></svg>
                        Provision DB
                    </button>
                </form>
                <form method="POST" action="{{ route('superadmin.tenants.migrate', $tenant->id) }}"
                      onsubmit="return confirm('Run pending migrations on this tenant database?')">
                    @csrf
                    <button type="submit" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-xs tw-font-medium tw-text-amber-700 tw-border tw-border-amber-200 hover:tw-bg-amber-50 tw-transition-colors tw-bg-transparent tw-cursor-pointer">
                        <svg class="tw-w-3.5 tw-h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Run Migrations
                    </button>
                </form>
            </div>
            @endif
        </div>

        @if ($tenant)
        <div class="tw-p-5 tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-5">
            <dl class="tw-space-y-3 tw-text-sm">
                <div>
                    <dt class="tw-text-xs tw-font-medium tw-text-gray-500">Tenant ID</dt>
                    <dd class="tw-mt-0.5"><code class="tw-text-xs tw-bg-gray-100 tw-px-1.5 tw-py-0.5 tw-rounded">{{ $tenant->id }}</code></dd>
                </div>
                <div>
                    <dt class="tw-text-xs tw-font-medium tw-text-gray-500">Database</dt>
                    <dd class="tw-mt-0.5"><code class="tw-text-xs tw-bg-gray-100 tw-px-1.5 tw-py-0.5 tw-rounded">tenant{{ $tenant->id }}</code></dd>
                </div>
                @if ($tenant->package_id)
                <div>
                    <dt class="tw-text-xs tw-font-medium tw-text-gray-500">Package ID</dt>
                    <dd class="tw-mt-0.5 tw-text-gray-700">{{ $tenant->package_id }}</dd>
                </div>
                @endif
            </dl>

            <div class="md:tw-col-span-2">
                <p class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider tw-mb-3">Domains / Subdomains</p>
                <div class="tw-rounded-lg tw-border tw-border-gray-200 tw-overflow-hidden">
                    <table class="tw-w-full tw-text-sm">
                        <thead class="tw-bg-gray-50">
                            <tr>
                                <th class="tw-px-4 tw-py-2 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500">Domain</th>
                                <th class="tw-px-4 tw-py-2 tw-w-20"></th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-gray-100">
                            @forelse ($tenant->domains as $domain)
                            <tr>
                                <td class="tw-px-4 tw-py-2"><code class="tw-text-xs">{{ $domain->domain }}</code></td>
                                <td class="tw-px-4 tw-py-2">
                                    <form method="POST" action="{{ route('superadmin.domains.destroy', $domain->id) }}"
                                          onsubmit="return confirm('Remove this domain?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="tw-text-xs tw-text-red-600 hover:tw-underline tw-bg-transparent tw-border-0 tw-cursor-pointer tw-p-0">Remove</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="tw-px-4 tw-py-3 tw-text-center tw-text-xs tw-text-gray-400">No domains assigned</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <form method="POST" action="{{ route('superadmin.domains.store') }}" class="tw-mt-3 tw-flex tw-gap-2">
                    @csrf
                    <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                    <input type="text" name="domain" class="form-control input-sm tw-flex-1"
                           placeholder="e.g. mybiz.app.com or pos.mybiz.com" required>
                    <button type="submit" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-xs tw-font-medium tw-bg-indigo-600 hover:tw-bg-indigo-700 tw-text-white tw-transition-colors tw-shrink-0 tw-border-0 tw-cursor-pointer">
                        Add Domain
                    </button>
                </form>
                <p class="tw-text-xs tw-text-gray-400 tw-mt-1">Enter full domain or subdomain (no http://)</p>
            </div>
        </div>
        @else
        <div class="tw-p-5">
            <div class="tw-flex tw-items-start tw-gap-3 tw-p-4 tw-rounded-lg tw-bg-amber-50 tw-border tw-border-amber-200">
                <svg class="tw-w-4 tw-h-4 tw-text-amber-500 tw-shrink-0 tw-mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="tw-text-sm tw-text-amber-800">
                    No tenant provisioned for this business.
                    <a href="{{ route('superadmin.tenants.index') }}" class="tw-text-amber-700 hover:tw-underline tw-font-medium">Visit the Tenant portal</a> to provision.
                </p>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Business Locations ── --}}
    <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
        <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
            <svg class="tw-w-4 tw-h-4 tw-text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">@lang('superadmin::lang.business_location')</h2>
        </div>
        <div class="tw-p-5 tw-overflow-x-auto">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Location ID</th>
                        <th>Landmark</th>
                        <th>City</th>
                        <th>Zip Code</th>
                        <th>State</th>
                        <th>Country</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($business->locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td>{{ $location->location_id }}</td>
                        <td>{{ $location->landmark }}</td>
                        <td>{{ $location->city }}</td>
                        <td>{{ $location->zip_code }}</td>
                        <td>{{ $location->state }}</td>
                        <td>{{ $location->country }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="tw-text-center tw-text-gray-400 tw-py-4">No locations</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Subscriptions ── --}}
    <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
        <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
            <svg class="tw-w-4 tw-h-4 tw-text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 11a8.1 8.1 0 0 0-15.5-2m-.5-4v4h4M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/>
            </svg>
            <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">@lang('superadmin::lang.package_subscription')</h2>
        </div>
        <div class="tw-p-5 tw-overflow-x-auto">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Package Name</th>
                        <th>Start Date</th>
                        <th>Trial End Date</th>
                        <th>End Date</th>
                        <th>Paid Via</th>
                        <th>Transaction ID</th>
                        <th>Created At</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($business->subscriptions as $subscription)
                    <tr>
                        <td>{{ $subscription->package_details['name'] }}</td>
                        <td>@if(!empty($subscription->start_date)) {{ @format_date($subscription->start_date) }} @endif</td>
                        <td>@if(!empty($subscription->trial_end_date)) {{ @format_date($subscription->trial_end_date) }} @endif</td>
                        <td>@if(!empty($subscription->end_date)) {{ @format_date($subscription->end_date) }} @endif</td>
                        <td>{{ $subscription->paid_via }}</td>
                        <td>{{ $subscription->payment_transaction_id }}</td>
                        <td>{{ $subscription->created_at }}</td>
                        <td>@if(!empty($subscription->created_user)) {{ $subscription->created_user->user_full_name }} @endif</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="tw-text-center tw-text-gray-400 tw-py-4">No subscriptions</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Users ── --}}
    <div class="tw-bg-white tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-shadow-sm">
        <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100 tw-flex tw-items-center tw-gap-2">
            <svg class="tw-w-4 tw-h-4 tw-text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h2 class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wider">{{ __('user.all_users') }}</h2>
        </div>
        <div class="tw-p-5 tw-overflow-x-auto">
            <table class="table table-bordered table-striped" id="users_table">
                <thead>
                    <tr>
                        <th>@lang('business.username')</th>
                        <th>@lang('user.name')</th>
                        <th>@lang('user.role')</th>
                        <th>@lang('business.email')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('superadmin::business.update_password_modal')
</section>
@stop

@section('javascript')
<script>
$(document).ready(function () {
    var users_table = $('#users_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/superadmin/users/' + "{{ $business->id }}",
        columnDefs: [{ targets: [4], orderable: false, searchable: false }],
        columns: [
            { data: 'username' },
            { data: 'full_name' },
            { data: 'role' },
            { data: 'email' },
            { data: 'action' },
        ]
    });
});

$(document).on('click', '.update_user_password', function (e) {
    e.preventDefault();
    $('form#password_update_form, #user_id').val($(this).data('user_id'));
    $('span#user_name').text($(this).data('user_name'));
    $('#update_password_modal').modal('show');
});

var password_update_form_validator = $('form#password_update_form').validate();

$('#update_password_modal').on('hidden.bs.modal', function () {
    password_update_form_validator.resetForm();
    $('form#password_update_form')[0].reset();
});

$(document).on('submit', 'form#password_update_form', function (e) {
    e.preventDefault();
    $(this).find('button[type="submit"]').attr('disabled', true);
    $.ajax({
        method: 'post',
        url: $(this).attr('action'),
        dataType: 'json',
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                $('#update_password_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
            $('form#password_update_form').find('button[type="submit"]').attr('disabled', false);
        },
    });
});
</script>
@endsection
