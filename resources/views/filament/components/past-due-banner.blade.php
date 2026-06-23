@php
    $tenant = \App\Support\TenantContext::current();
@endphp

@if($tenant && $tenant->stripe_subscription_status === 'past_due')
<div style="background:#fffbeb;border-bottom:2px solid #fbbf24;width:100%;box-sizing:border-box;">
    <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;flex-wrap:wrap;">
        <svg style="width:18px;height:18px;color:#d97706;flex-shrink:0;" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <span style="font-size:13px;font-weight:600;color:#78350f;flex:1;">
            {{ __('Payment required — your subscription is past due. Update your payment method to avoid service interruption.') }}
        </span>
        <a href="{{ route('billing.portal') }}"
           style="flex-shrink:0;display:inline-block;padding:5px 14px;background:#d97706;color:#fff;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
            {{ __('Update Payment') }} →
        </a>
    </div>
</div>
@endif
