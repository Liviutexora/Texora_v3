@php
    $tenant = \App\Support\TenantContext::current();
    if ($tenant) {
        $billing     = app(\App\Services\BillingService::class);
        $trialEndsAt = $billing->trialEndsAt($tenant);
        $daysLeft    = $trialEndsAt ? max(0, (int) now()->diffInDays($trialEndsAt, false) + 1) : null;
        $showBanner  = $trialEndsAt && $daysLeft !== null && $daysLeft <= 7
                       && $tenant->stripe_subscription_status === 'trialing';
    } else {
        $showBanner = false;
        $daysLeft   = null;
        $trialEndsAt = null;
    }
@endphp

@if($showBanner)
<div style="background:#eff6ff;border-bottom:2px solid #3b82f6;width:100%;box-sizing:border-box;">
    <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;flex-wrap:wrap;">
        <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span style="font-size:13px;font-weight:600;color:#1e40af;flex:1;">
            {{ __('Your free trial ends in :days :unit (:date). Add a payment method to keep your booking page live.', ['days' => $daysLeft, 'unit' => Str::plural(__('day'), $daysLeft), 'date' => $trialEndsAt->format('d M Y')]) }}
        </span>
        <a href="{{ route('filament.tenant.pages.billing') }}"
           style="flex-shrink:0;display:inline-block;padding:5px 14px;background:#2563eb;color:#fff;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
            {{ __('Manage Subscription') }} →
        </a>
    </div>
</div>
@endif
