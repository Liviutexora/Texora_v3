@php
    $tenant = \App\Support\TenantContext::current();
    if (! $tenant) { return; }

    $plan     = $tenant->plan;
    $isFree   = ! $plan || $plan->isFree();
    $status   = $tenant->stripe_subscription_status ?? ($tenant->status === 'active' ? 'active' : null);
    $isActive = in_array($status, ['active', 'trialing']) || ($isFree && $tenant->status === 'active');

    $displayPrice = null;
    if ($plan && ! $isFree) {
        $price = $plan->activePrices->firstWhere('billing_cycle', 'monthly')
              ?? $plan->activePrices->first();
        if ($price) {
            $cycle = match($price->billing_cycle) {
                'yearly'  => 'yr',
                'weekly'  => 'wk',
                default   => 'mo',
            };
            $displayPrice = '$' . number_format((float) $price->price, 0) . '/' . $cycle;
        }
    }

    $billingUrl = route('filament.tenant.pages.billing');
@endphp

<div x-data x-show="$store.sidebar?.isOpen ?? true" style="padding:10px 12px 14px;">
    <a href="{{ $billingUrl }}" style="display:block;text-decoration:none;">
        <div style="
            position:relative;
            border-radius:12px;
            padding:11px 13px 10px;
            overflow:hidden;
            background:{{ $isFree
                ? 'linear-gradient(135deg,#f5f3ff 0%,#ede9fe 100%)'
                : 'linear-gradient(135deg,rgba(124,58,237,0.18) 0%,rgba(139,92,246,0.10) 100%)' }};
            border:1px solid {{ $isFree ? '#ddd6fe' : 'rgba(139,92,246,0.35)' }};
            box-shadow:0 1px 3px rgba(124,58,237,0.08);
            transition:box-shadow .15s,border-color .15s;
        "
        onmouseover="this.style.boxShadow='0 3px 10px rgba(124,58,237,0.15)';this.style.borderColor='{{ $isFree ? '#c4b5fd' : 'rgba(139,92,246,0.55)' }}';"
        onmouseout="this.style.boxShadow='0 1px 3px rgba(124,58,237,0.08)';this.style.borderColor='{{ $isFree ? '#ddd6fe' : 'rgba(139,92,246,0.35)' }}';">

            {{-- Decorative glow blob --}}
            <div style="position:absolute;top:-18px;right:-18px;width:60px;height:60px;border-radius:50%;background:radial-gradient(circle,rgba(139,92,246,0.2) 0%,transparent 70%);pointer-events:none;"></div>

            {{-- Top row: plan name + status pill --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">

                {{-- Icon + plan name --}}
                <div style="display:flex;align-items:center;gap:5px;">
                    @if($isFree)
                    {{-- Sparkle icon for free plan --}}
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;opacity:0.7;">
                        <path d="M12 2L14.4 9.6L22 12L14.4 14.4L12 22L9.6 14.4L2 12L9.6 9.6L12 2Z" fill="#7c3aed"/>
                    </svg>
                    @else
                    {{-- Crown icon for paid plan --}}
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                        <path d="M2 17L4 7L8.5 12L12 4L15.5 12L20 7L22 17H2Z" fill="#7c3aed" opacity="0.8"/>
                    </svg>
                    @endif
                    <span style="font-size:11px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#5b21b6;">
                        {{ $plan?->name ?? __('Free') }} {{ __('Plan') }}
                    </span>
                </div>

                {{-- Status pill --}}
                <span style="
                    display:inline-flex;align-items:center;gap:3px;
                    font-size:10px;font-weight:600;
                    padding:2px 7px;border-radius:20px;
                    color:{{ $isActive ? '#065f46' : '#991b1b' }};
                    background:{{ $isActive ? 'rgba(52,211,153,0.15)' : 'rgba(248,113,113,0.15)' }};
                    border:1px solid {{ $isActive ? 'rgba(52,211,153,0.3)' : 'rgba(248,113,113,0.3)' }};
                ">
                    <span style="
                        width:5px;height:5px;border-radius:50%;flex-shrink:0;
                        background:{{ $isActive ? '#10b981' : '#f87171' }};
                        {{ $isActive ? 'box-shadow:0 0 4px #10b981;' : '' }}
                    "></span>
                    {{ $isActive ? __('Active') : __('Inactive') }}
                </span>
            </div>

            {{-- Bottom row: price/tagline + CTA --}}
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:11px;color:#7c3aed;opacity:0.6;">
                    @if($displayPrice)
                        {{ $displayPrice }}
                    @elseif($isFree)
                        {{ __('Free forever') }}
                    @endif
                </span>
                <span style="display:inline-flex;align-items:center;gap:2px;font-size:10.5px;font-weight:600;color:#6d28d9;opacity:0.8;">
                    {{ $isFree ? __('Upgrade') : __('Manage') }}
                    <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </div>
        </div>
    </a>
</div>
