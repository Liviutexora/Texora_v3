<x-filament-panels::page>
@php
    $tenant   = \App\Support\TenantContext::current();
    $billing  = app(\App\Services\BillingService::class);
    $plan     = $tenant?->plan;
    $status   = $tenant?->stripe_subscription_status;

    $trialEndsAt = $tenant ? $billing->trialEndsAt($tenant) : null;
    $trialDays   = $trialEndsAt ? max(1, (int) now()->diffInDays($trialEndsAt, false) + 1) : null;

    // Fall back to tenant->status when stripe_subscription_status is null
    // (admin-assigned plans, legacy tenants, or pre-Stripe registrations)
    $effectiveStatus = $status ?? ($tenant?->status === 'active' ? 'active' : null);

    $badgeColor = match($effectiveStatus) {
        'active'   => 'success',
        'trialing' => 'info',
        'past_due' => 'warning',
        'paused'   => 'gray',
        'canceled' => 'danger',
        default    => 'gray',
    };
    $badgeLabel = match($effectiveStatus) {
        'active'   => __('Active'),
        'trialing' => __('Trial'),
        'past_due' => __('Past Due'),
        'paused'   => __('Paused'),
        'canceled' => __('Cancelled'),
        default    => __('Free'),
    };
@endphp

<style>
.billing-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}
@media (min-width: 1024px) {
    .billing-layout {
        grid-template-columns: 1fr 1fr 1fr;
    }
    .billing-main { grid-column: span 2; }
    .billing-aside { grid-column: span 1; }
}
.billing-main,
.billing-aside {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
</style>

{{-- ─── PAST DUE ALERT ──────────────────────────────────────────────────── --}}
@if($status === 'past_due')
<div class="mb-1">
    <x-filament::section icon="heroicon-o-exclamation-triangle" icon-color="warning" color="warning">
        <x-slot name="heading">{{ __('Payment required') }}</x-slot>
        <x-slot name="description">{{ __('Your subscription is past due. Update your payment method to avoid service interruption.') }}</x-slot>
        <x-filament::button color="warning" tag="a" :href="route('billing.portal')">
            Update Payment Method
        </x-filament::button>
    </x-filament::section>
</div>
@endif

{{-- ─── TRIAL COUNTDOWN ALERT ───────────────────────────────────────────── --}}
@if($trialDays !== null && $status === 'trialing' && $trialDays <= 7)
<div class="mb-1">
    <x-filament::section icon="heroicon-o-clock" icon-color="info" color="info">
        <x-slot name="heading">{{ trans_choice('admin.Trial ending in :count day|Trial ending in :count days', $trialDays, ['count' => $trialDays]) }}</x-slot>
        <x-slot name="description">{{ __('Your trial ends on :date. Add a payment method to keep your booking page live.', ['date' => $trialEndsAt->format('d M Y')]) }}</x-slot>
        <x-filament::button color="info" tag="a" :href="route('billing.portal')">
            {{ __('Add Payment Method') }}
        </x-filament::button>
    </x-filament::section>
</div>
@endif

{{-- ─── MAIN GRID ───────────────────────────────────────────────────────── --}}
<div class="billing-layout">

    {{-- ── LEFT / MAIN COLUMN ─────────────────────────────────────────── --}}
    <div class="billing-main">

        {{-- Current Plan Card --}}
        <x-filament::section>
            <x-slot name="heading">
                <span class="inline-flex items-center gap-2.5">
                    {{ $plan?->name ?? 'Free Plan' }}
                    <x-filament::badge :color="$badgeColor">{{ $badgeLabel }}</x-filament::badge>
                </span>
            </x-slot>
            <x-slot name="description">
                @if($plan && ! $plan->isFree())
                    {{ $plan->priceDisplay() }}
                @else
                    {{ __('No charge') }}
                @endif
                @if($status === 'trialing' && $trialEndsAt)
                    &nbsp;·&nbsp;{{ __('Trial ends') }} <strong>{{ $trialEndsAt->format('d M Y') }}</strong>
                @endif
            </x-slot>

            {{-- Active subscriber: portal action buttons --}}
            @if($billingState === 'active')
            <div class="flex flex-wrap gap-2.5 pt-1">
                <x-filament::button color="gray" tag="a" :href="route('billing.portal')" icon="heroicon-o-credit-card">
                    Update Payment
                </x-filament::button>
                @if($status === 'paused')
                <x-filament::button color="primary" tag="a" :href="route('billing.portal')">
                    Reactivate Subscription
                </x-filament::button>
                @endif
                <x-filament::button color="gray" x-data x-on:click="$dispatch('open-downgrade-modal')" icon="heroicon-o-arrows-right-left">
                    Change Plan
                </x-filament::button>
                <x-filament::button color="danger" outlined x-data x-on:click="$dispatch('open-cancel-modal')" icon="heroicon-o-x-circle">
                    Cancel Subscription
                </x-filament::button>
            </div>
            @endif

            {{-- Manual / admin-assigned plan: no Stripe portal available --}}
            @if($billingState === 'manual')
            <p class="text-[13px] text-gray-500 mt-1">
                {{ __('Your plan is managed by your account administrator. Contact support to make changes.') }}
            </p>
            @endif

            {{-- Free / canceled: upgrade CTA --}}
            @if($billingState === 'free' || $billingState === 'checkout_only')
                @if($billingState === 'checkout_only')
                <p class="text-[13px] text-gray-500 mb-3">
                    Your subscription has ended. Resubscribe to restore full access.
                </p>
                @endif
                <div class="flex flex-wrap gap-2.5 pt-1">
                    @foreach($paidPlans as $pp)
                        @foreach($pp['active_prices'] as $price)
                        <form method="POST" action="{{ route('billing.checkout', ['plan' => $pp['slug'], 'cycle' => $price['billing_cycle']]) }}">
                            @csrf
                            <x-filament::button
                                type="submit"
                                color="primary"
                                icon="heroicon-o-arrow-up-circle"
                                x-data="{ loading: false }"
                                x-on:click="loading = true"
                            >
                                <span x-show="!loading">
                                    {{ $pp['name'] }} — {{ $price['billing_cycle'] === 'yearly' ? 'Annually' : ($price['billing_cycle'] === 'weekly' ? 'Weekly' : 'Monthly') }}
                                    &nbsp;${{ number_format($price['price'], 0) }}/{{ $price['billing_cycle'] === 'yearly' ? 'yr' : ($price['billing_cycle'] === 'weekly' ? 'wk' : 'mo') }}
                                </span>
                                <span x-show="loading" class="inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" style="animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                                        <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    Redirecting…
                                </span>
                            </x-filament::button>
                        </form>
                        @endforeach
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Plan Usage / Limit Bars --}}
        @if($showLimitBars)
        <x-filament::section>
            <x-slot name="heading">{{ __('Plan Usage') }}</x-slot>

            <div class="flex flex-col gap-6">

                {{-- Providers --}}
                @php
                    $provPct     = $maxProviders ? min(100, round(($providerCount / $maxProviders) * 100)) : 0;
                    $provAtLimit = $maxProviders !== null && $providerCount >= $maxProviders;
                @endphp
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[13px] font-medium">{{ __('Providers') }}</span>
                        <span @class(['text-[13px]', 'font-semibold text-amber-600' => $provAtLimit, 'font-normal text-gray-500' => !$provAtLimit])>
                            {{ $providerCount }} / {{ $maxProviders !== null ? $maxProviders : '∞' }}
                        </span>
                    </div>
                    @if($maxProviders !== null)
                    <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div style="width:{{ $provPct }}%;background:{{ $provAtLimit ? '#f59e0b' : '#7c3aed' }};" class="h-2 rounded-full"></div>
                    </div>
                    @if($provAtLimit)
                    <p class="text-xs text-amber-500 mt-1.5">
                        @if($billingState !== 'active') {{ __('Limit reached — upgrade your plan for unlimited providers.') }}
                        @else {{ __('Limit reached — change plan for more providers.') }} @endif
                    </p>
                    @endif
                    @endif
                </div>

                {{-- Services --}}
                @php
                    $svcPct     = $maxServices ? min(100, round(($serviceCount / $maxServices) * 100)) : 0;
                    $svcAtLimit = $maxServices !== null && $serviceCount >= $maxServices;
                @endphp
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[13px] font-medium">{{ __('Services') }}</span>
                        <span @class(['text-[13px]', 'font-semibold text-amber-600' => $svcAtLimit, 'font-normal text-gray-500' => !$svcAtLimit])>
                            {{ $serviceCount }} / {{ $maxServices !== null ? $maxServices : '∞' }}
                        </span>
                    </div>
                    @if($maxServices !== null)
                    <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div style="width:{{ $svcPct }}%;background:{{ $svcAtLimit ? '#f59e0b' : '#7c3aed' }};" class="h-2 rounded-full"></div>
                    </div>
                    @if($svcAtLimit)
                    <p class="text-xs text-amber-500 mt-1.5">
                        @if($billingState !== 'active') {{ __('Limit reached — upgrade your plan for unlimited services.') }}
                        @else {{ __('Limit reached — change plan for more services.') }} @endif
                    </p>
                    @endif
                    @endif
                </div>

                {{-- Bookings this month --}}
                @if($maxBookingsPerMonth !== null)
                @php
                    $bkPct     = min(100, round(($bookingsThisMonth / $maxBookingsPerMonth) * 100));
                    $bkAtLimit = $bookingsThisMonth >= $maxBookingsPerMonth;
                @endphp
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[13px] font-medium">{{ __('Bookings this month') }}</span>
                        <span @class(['text-[13px]', 'font-semibold text-amber-600' => $bkAtLimit, 'font-normal text-gray-500' => !$bkAtLimit])>
                            {{ $bookingsThisMonth }} / {{ $maxBookingsPerMonth }}
                        </span>
                    </div>
                    <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div style="width:{{ $bkPct }}%;background:{{ $bkAtLimit ? '#f59e0b' : '#7c3aed' }};" class="h-2 rounded-full"></div>
                    </div>
                    @if($bkAtLimit)
                    <p class="text-xs text-amber-500 mt-1.5">{{ __('Monthly booking limit reached — upgrade to accept more bookings.') }}</p>
                    @endif
                </div>
                @endif

            </div>
        </x-filament::section>
        @endif

        {{-- Invoice History --}}
        @if(count($invoices) > 0)
        <x-filament::section>
            <x-slot name="heading">{{ __('Invoice History') }}</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[13px]">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left pr-3 pb-2.5 text-gray-500 font-medium">{{ __('Invoice') }}</th>
                            <th class="text-left pr-3 pb-2.5 text-gray-500 font-medium">{{ __('Date') }}</th>
                            <th class="text-left pr-3 pb-2.5 text-gray-500 font-medium">{{ __('Amount') }}</th>
                            <th class="text-left pr-3 pb-2.5 text-gray-500 font-medium">{{ __('Status') }}</th>
                            <th class="text-right pb-2.5 text-gray-500 font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                        <tr class="border-b border-gray-50">
                            <td class="py-2.5 pr-3 font-mono text-[11px] text-gray-400">
                                {{ $inv['number'] ?? substr($inv['id'], 0, 14) . '…' }}
                            </td>
                            <td class="py-2.5 pr-3 text-gray-700">{{ $inv['created'] }}</td>
                            <td class="py-2.5 pr-3 font-medium text-gray-900">
                                {{ $inv['currency'] }} ${{ number_format($inv['amount'], 2) }}
                            </td>
                            <td class="py-2.5 pr-3">
                                <x-filament::badge :color="$inv['status'] === 'paid' ? 'success' : 'gray'" size="sm">
                                    {{ ucfirst($inv['status']) }}
                                </x-filament::badge>
                            </td>
                            <td class="py-2.5 text-right">
                                @if($inv['hosted_url'])
                                <a href="{{ $inv['hosted_url'] }}" target="_blank" rel="noopener"
                                   class="text-violet-600 text-xs font-medium no-underline mr-2.5">{{ __('View') }}</a>
                                @endif
                                @if($inv['pdf_url'])
                                <a href="{{ $inv['pdf_url'] }}" target="_blank" rel="noopener"
                                   class="text-gray-500 text-xs no-underline">{{ __('PDF') }}</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
        @endif

    </div>{{-- end billing-main --}}

    {{-- ── RIGHT / ASIDE COLUMN ────────────────────────────────────────── --}}
    <div class="billing-aside">

        <x-filament::section>
            <x-slot name="heading">{{ __('Subscription Details') }}</x-slot>
            <dl class="flex flex-col gap-3.5 text-[13px] mt-1">
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">{{ __('Plan') }}</dt>
                    <dd class="font-semibold">{{ $plan?->name ?? __('Free') }}</dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">{{ __('Status') }}</dt>
                    <dd><x-filament::badge :color="$badgeColor">{{ $badgeLabel }}</x-filament::badge></dd>
                </div>
                @if($effectiveStatus === 'trialing' && $trialEndsAt)
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">{{ __('Trial ends') }}</dt>
                    <dd class="font-medium">{{ $trialEndsAt->format('d M Y') }}</dd>
                </div>
                @endif
                @if($plan?->max_providers !== null)
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">{{ __('Providers') }}</dt>
                    <dd class="font-medium">{{ __('Up to :n', ['n' => $plan->max_providers]) }}</dd>
                </div>
                @endif
                @if($plan?->max_services !== null)
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">{{ __('Services') }}</dt>
                    <dd class="font-medium">{{ __('Up to :n', ['n' => $plan->max_services]) }}</dd>
                </div>
                @endif
            </dl>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('Need help?') }}</x-slot>
            <p class="text-[13px] text-gray-500 leading-relaxed mb-3.5">
                {{ __("Questions about your subscription? Contact us and we'll sort it out.") }}
            </p>
            @php
                $supportEmail = \App\Models\Setting::get('support_email')
                    ?: \App\Models\Setting::get('site_email')
                    ?: \App\Models\Setting::get('contact_email')
                    ?: config('mail.from.address');
            @endphp
            @if($supportEmail)
            <x-filament::button color="gray" tag="a" :href="'mailto:' . $supportEmail" icon="heroicon-o-envelope" outlined>
                {{ $supportEmail }}
            </x-filament::button>
            @endif
        </x-filament::section>

    </div>{{-- end billing-aside --}}

</div>{{-- end billing-layout --}}


{{-- ─── DOWNGRADE MODAL ────────────────────────────────────────────────── --}}
<div
    x-data="{ open: false }"
    x-on:open-downgrade-modal.window="open = true"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="w-full max-w-[480px]" x-on:click.stop>
        <x-filament::section icon="heroicon-o-arrows-right-left">
            <x-slot name="heading">{{ __('Change your plan') }}</x-slot>
            <p class="text-sm text-gray-600 mb-4">
                {{ __("You're about to open the Stripe billing portal to change your plan.") }}
            </p>
            @if($providerCount > 0 || $serviceCount > 0)
            <div class="bg-amber-50 border border-yellow-300 rounded-lg p-3.5 mb-[18px] text-xs text-amber-900">
                <p class="font-semibold mb-1.5">{{ __('Your current usage:') }}</p>
                <p class="mb-0.5">• {{ $providerCount }} {{ Str::plural('provider', $providerCount) }}</p>
                <p>• {{ $serviceCount }} {{ Str::plural('service', $serviceCount) }}</p>
                <p class="mt-2.5 text-amber-950">
                    {{ __("Downgrading won't deactivate existing records, but you won't be able to add more until you're within the new plan's limits.") }}
                </p>
            </div>
            @endif
            <div class="flex gap-2.5">
                <x-filament::button color="gray" x-on:click="open = false">{{ __('Cancel') }}</x-filament::button>
                <x-filament::button color="primary" tag="a" :href="route('billing.portal')">{{ __('Continue to Portal') }}</x-filament::button>
            </div>
        </x-filament::section>
    </div>
</div>

{{-- ─── CANCEL MODAL ───────────────────────────────────────────────────── --}}
<div
    x-data="{ open: false }"
    x-on:open-cancel-modal.window="open = true"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
>
    <div class="w-full max-w-[480px]" x-on:click.stop>
        <x-filament::section icon="heroicon-o-x-circle" icon-color="danger">
            <x-slot name="heading">{{ __('Cancel your subscription?') }}</x-slot>
            <p class="text-sm text-gray-600 mb-2.5">
                {{ __("Cancelling will stop your subscription at the end of the current billing period. You'll keep access until then.") }}
            </p>
            <p class="text-sm text-gray-600 mb-[18px]">
                {{ __('You can reactivate at any time from this page.') }}
            </p>
            <div class="flex gap-2.5">
                <x-filament::button color="gray" x-on:click="open = false">{{ __('Keep Subscription') }}</x-filament::button>
                <x-filament::button color="danger" tag="a" :href="route('billing.portal')">{{ __('Cancel in Portal') }}</x-filament::button>
            </div>
        </x-filament::section>
    </div>
</div>

{{-- @keyframes spin extracted to resources/css/components.css --}}

</x-filament-panels::page>
