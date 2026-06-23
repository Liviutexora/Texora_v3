<x-filament-panels::page>

{{-- ═══════════════════════════════════════════════════════════════════════════
     Payment Settings — gateway picker + conditional API fields
     ═══════════════════════════════════════════════════════════════════════════ --}}

<div class="ps-wrap">

    {{-- ── Enable toggle ─────────────────────────────────────────────────── --}}
    <div class="ps-card ps-mb">
        <div class="ps-row-between">
            <div>
                <div class="ps-label">{{ __('Online Payments') }}</div>
                <div class="ps-hint">{{ __('Collect payment when customers book paid services online.') }}</div>
            </div>
            <button wire:click="$toggle('paymentEnabled')" type="button"
                    class="ps-toggle {{ $paymentEnabled ? 'ps-toggle--on' : '' }}"
                    role="switch" aria-checked="{{ $paymentEnabled ? 'true' : 'false' }}">
                <span class="ps-toggle-knob"></span>
            </button>
        </div>

        @if ($paymentEnabled)
        <div class="ps-divider"></div>
        <div class="ps-row-between">
            <div>
                <div class="ps-label">{{ __('Require payment at booking') }}</div>
                <div class="ps-hint">{{ __('Confirmation emails are sent only after payment succeeds.') }}</div>
            </div>
            <button wire:click="$toggle('requirePaymentAtBooking')" type="button"
                    class="ps-toggle {{ $requirePaymentAtBooking ? 'ps-toggle--on' : '' }}"
                    role="switch" aria-checked="{{ $requirePaymentAtBooking ? 'true' : 'false' }}">
                <span class="ps-toggle-knob"></span>
            </button>
        </div>
        @endif
    </div>

    @if ($paymentEnabled)

    {{-- ── Gateway picker ──────────────────────────────────────────────── --}}
    <div class="ps-section-title">{{ __('Payment Gateway') }}</div>
    <div class="ps-gateways ps-mb">

        {{-- Stripe --}}
        <button wire:click="selectGateway('stripe')" type="button"
                class="ps-gw {{ $activeGateway === 'stripe' ? 'ps-gw--active' : '' }}">
            <div class="ps-gw-logo ps-gw-logo--stripe">
                <svg width="22" height="22" viewBox="0 0 48 48" fill="none">
                    <rect width="48" height="48" rx="10" fill="#635BFF"/>
                    <path d="M22.5 19.3c0-1 .9-1.4 2.3-1.4 2 0 4.6.6 6.6 1.7v-6.3c-2.2-.9-4.4-1.3-6.6-1.3-5.4 0-9 2.8-9 7.5 0 7.3 10 6.2 10 9.4 0 1.2-1 1.6-2.5 1.6-2.2 0-5-.9-7.2-2.2v6.4c2.4 1 4.9 1.5 7.2 1.5 5.5 0 9.3-2.7 9.3-7.5-.1-7.9-10.1-6.5-10.1-9.4z" fill="#fff"/>
                </svg>
            </div>
            <div class="ps-gw-name">Stripe</div>
            @if ($activeGateway === 'stripe')
            <div class="ps-gw-check">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            @endif
        </button>

        {{-- Razorpay --}}
        <button wire:click="selectGateway('razorpay')" type="button"
                class="ps-gw {{ $activeGateway === 'razorpay' ? 'ps-gw--active' : '' }}">
            <div class="ps-gw-logo ps-gw-logo--razorpay">
                <svg width="22" height="22" viewBox="0 0 48 48" fill="none">
                    <rect width="48" height="48" rx="10" fill="#3395FF"/>
                    <path d="M30 12L18.5 26h6.8L20 36h2l11-15h-7L31 12h-1z" fill="#fff"/>
                </svg>
            </div>
            <div class="ps-gw-name">Razorpay</div>
            @if ($activeGateway === 'razorpay')
            <div class="ps-gw-check">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            @endif
        </button>

        {{-- PayPal --}}
        <button wire:click="selectGateway('paypal')" type="button"
                class="ps-gw {{ $activeGateway === 'paypal' ? 'ps-gw--active' : '' }}">
            <div class="ps-gw-logo ps-gw-logo--paypal">
                <svg width="22" height="22" viewBox="0 0 48 48" fill="none">
                    <rect width="48" height="48" rx="10" fill="#003087"/>
                    <path d="M19 14h8c3.5 0 5.5 1.8 5 5-0.7 4.5-3.8 6-7.5 6H22l-1.5 9H16l3-20z" fill="#009cde"/>
                    <path d="M22 14h7.5c3.5 0 5 1.8 4.5 5-0.7 4.5-3.8 6-7.5 6H24l-1.5 9h-4.5l3-20h.5l-2 0z" fill="#fff" opacity=".15"/>
                    <path d="M24 22h2c1.5 0 2.5-.8 2.7-2.3.2-1.2-.5-1.7-1.7-1.7h-2L24 22z" fill="#fff"/>
                </svg>
            </div>
            <div class="ps-gw-name">PayPal</div>
            @if ($activeGateway === 'paypal')
            <div class="ps-gw-check">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            @endif
        </button>

        {{-- Paddle --}}
        <button wire:click="selectGateway('paddle')" type="button"
                class="ps-gw {{ $activeGateway === 'paddle' ? 'ps-gw--active' : '' }}">
            <div class="ps-gw-logo ps-gw-logo--paddle">
                <svg width="22" height="22" viewBox="0 0 48 48" fill="none">
                    <rect width="48" height="48" rx="10" fill="#1A1A2E"/>
                    <ellipse cx="20" cy="24" rx="5" ry="8" fill="#44DB5E"/>
                    <rect x="15" y="16" width="4" height="16" rx="2" fill="#44DB5E"/>
                    <rect x="27" y="20" width="4" height="8" rx="2" fill="#44DB5E" opacity=".6"/>
                </svg>
            </div>
            <div class="ps-gw-name">Paddle</div>
            @if ($activeGateway === 'paddle')
            <div class="ps-gw-check">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            @endif
        </button>

    </div>

    {{-- ── API credentials for selected gateway ───────────────────────── --}}
    <div class="ps-card ps-mb">

        {{-- Stripe -------------------------------------------------------- --}}
        @if ($activeGateway === 'stripe')
        <div class="ps-cred-header">
            <div class="ps-gw-logo ps-gw-logo--stripe ps-gw-logo--sm">
                <svg width="16" height="16" viewBox="0 0 48 48" fill="none">
                    <rect width="48" height="48" rx="10" fill="#635BFF"/>
                    <path d="M22.5 19.3c0-1 .9-1.4 2.3-1.4 2 0 4.6.6 6.6 1.7v-6.3c-2.2-.9-4.4-1.3-6.6-1.3-5.4 0-9 2.8-9 7.5 0 7.3 10 6.2 10 9.4 0 1.2-1 1.6-2.5 1.6-2.2 0-5-.9-7.2-2.2v6.4c2.4 1 4.9 1.5 7.2 1.5 5.5 0 9.3-2.7 9.3-7.5-.1-7.9-10.1-6.5-10.1-9.4z" fill="#fff"/>
                </svg>
            </div>
            <div>
                <div class="ps-cred-title">Stripe {{ __('Credentials') }}</div>
                <div class="ps-cred-hint">{{ __('Find these in your Stripe Dashboard → Developers → API keys.') }}</div>
            </div>
        </div>
        <div class="ps-fields">
            <div class="ps-field">
                <label class="ps-field-label">{{ __('Publishable key') }}</label>
                <input wire:model="stripePublishableKey" type="text" class="ps-input" placeholder="pk_live_..." autocomplete="off">
            </div>
            <div class="ps-field">
                <label class="ps-field-label">{{ __('Secret key') }}</label>
                <input wire:model="stripeSecretKey" type="password" class="ps-input" placeholder="{{ __('Leave blank to keep existing') }}" autocomplete="new-password">
            </div>
            <div class="ps-field">
                <label class="ps-field-label">{{ __('Webhook secret') }}</label>
                <input wire:model="stripeWebhookSecret" type="password" class="ps-input" placeholder="{{ __('Leave blank to keep existing') }}" autocomplete="new-password">
                <div class="ps-field-hint">{{ __('Webhook endpoint: POST /stripe/webhook') }}</div>
            </div>
        </div>
        @endif

        {{-- Razorpay ------------------------------------------------------- --}}
        @if ($activeGateway === 'razorpay')
        <div class="ps-cred-header">
            <div class="ps-gw-logo ps-gw-logo--razorpay ps-gw-logo--sm">
                <svg width="16" height="16" viewBox="0 0 48 48" fill="none">
                    <rect width="48" height="48" rx="10" fill="#3395FF"/>
                    <path d="M30 12L18.5 26h6.8L20 36h2l11-15h-7L31 12h-1z" fill="#fff"/>
                </svg>
            </div>
            <div>
                <div class="ps-cred-title">Razorpay {{ __('Credentials') }}</div>
                <div class="ps-cred-hint">{{ __('Find these in Razorpay Dashboard → Settings → API Keys.') }}</div>
            </div>
        </div>
        <div class="ps-fields">
            <div class="ps-field">
                <label class="ps-field-label">{{ __('Key ID') }}</label>
                <input wire:model="razorpayKeyId" type="text" class="ps-input" placeholder="rzp_live_..." autocomplete="off">
            </div>
            <div class="ps-field">
                <label class="ps-field-label">{{ __('Key secret') }}</label>
                <input wire:model="razorpayKeySecret" type="password" class="ps-input" placeholder="{{ __('Leave blank to keep existing') }}" autocomplete="new-password">
            </div>
            <div class="ps-field">
                <label class="ps-field-label">{{ __('Webhook secret') }}</label>
                <input wire:model="razorpayWebhookSecret" type="password" class="ps-input" placeholder="{{ __('Leave blank to keep existing') }}" autocomplete="new-password">
                <div class="ps-field-hint">{{ __('Webhook endpoint: POST /razorpay/webhook') }}</div>
            </div>
        </div>
        @endif

        {{-- PayPal --------------------------------------------------------- --}}
        @if ($activeGateway === 'paypal')
        <div class="ps-cred-header">
            <div class="ps-gw-logo ps-gw-logo--paypal ps-gw-logo--sm">
                <svg width="16" height="16" viewBox="0 0 48 48" fill="none">
                    <rect width="48" height="48" rx="10" fill="#003087"/>
                    <path d="M19 14h8c3.5 0 5.5 1.8 5 5-0.7 4.5-3.8 6-7.5 6H22l-1.5 9H16l3-20z" fill="#009cde"/>
                    <path d="M24 22h2c1.5 0 2.5-.8 2.7-2.3.2-1.2-.5-1.7-1.7-1.7h-2L24 22z" fill="#fff"/>
                </svg>
            </div>
            <div>
                <div class="ps-cred-title">PayPal {{ __('Credentials') }}</div>
                <div class="ps-cred-hint">{{ __('Find these in PayPal Developer → My Apps & Credentials.') }}</div>
            </div>
        </div>
        <div class="ps-fields">
            <div class="ps-field ps-field--full">
                <label class="ps-field-label">{{ __('Client ID') }}</label>
                <input wire:model="paypalClientId" type="text" class="ps-input" placeholder="AX..." autocomplete="off">
            </div>
            <div class="ps-field">
                <label class="ps-field-label">{{ __('Client secret') }}</label>
                <input wire:model="paypalClientSecret" type="password" class="ps-input" placeholder="{{ __('Leave blank to keep existing') }}" autocomplete="new-password">
            </div>
            <div class="ps-field">
                <label class="ps-field-label">{{ __('Mode') }}</label>
                <select wire:model="paypalMode" class="ps-input ps-select">
                    <option value="sandbox">{{ __('Sandbox (testing)') }}</option>
                    <option value="live">{{ __('Live') }}</option>
                </select>
            </div>
        </div>
        @endif

        {{-- Paddle --------------------------------------------------------- --}}
        @if ($activeGateway === 'paddle')
        <div class="ps-cred-header">
            <div class="ps-gw-logo ps-gw-logo--paddle ps-gw-logo--sm">
                <svg width="16" height="16" viewBox="0 0 48 48" fill="none">
                    <rect width="48" height="48" rx="10" fill="#1A1A2E"/>
                    <ellipse cx="20" cy="24" rx="5" ry="8" fill="#44DB5E"/>
                    <rect x="15" y="16" width="4" height="16" rx="2" fill="#44DB5E"/>
                    <rect x="27" y="20" width="4" height="8" rx="2" fill="#44DB5E" opacity=".6"/>
                </svg>
            </div>
            <div>
                <div class="ps-cred-title">Paddle {{ __('Credentials') }}</div>
                <div class="ps-cred-hint">{{ __('Find these in Paddle Dashboard → Developer Tools → Authentication.') }}</div>
            </div>
        </div>
        <div class="ps-fields">
            <div class="ps-field ps-field--full">
                <label class="ps-field-label">{{ __('Vendor ID') }}</label>
                <input wire:model="paddleVendorId" type="text" class="ps-input" placeholder="12345" autocomplete="off">
            </div>
            <div class="ps-field">
                <label class="ps-field-label">{{ __('API key') }}</label>
                <input wire:model="paddleApiKey" type="password" class="ps-input" placeholder="{{ __('Leave blank to keep existing') }}" autocomplete="new-password">
            </div>
            <div class="ps-field">
                <label class="ps-field-label">{{ __('Webhook secret') }}</label>
                <input wire:model="paddleWebhookSecret" type="password" class="ps-input" placeholder="{{ __('Leave blank to keep existing') }}" autocomplete="new-password">
                <div class="ps-field-hint">{{ __('Webhook endpoint: POST /paddle/webhook') }}</div>
            </div>
        </div>
        @endif

    </div>
    @endif

    {{-- ── Offline payment methods ────────────────────────────────────── --}}
    <div class="ps-section-title">{{ __('Offline Payment Methods') }}</div>
    <div class="ps-card ps-mb">
        <div class="ps-hint ps-hint--block">{{ __('Allow staff to record payments taken in person via the admin panel.') }}</div>
        <div class="ps-offline-methods">
            <label class="ps-offline-item">
                <input wire:model="offlineCashEnabled" type="checkbox" class="ps-checkbox">
                <div class="ps-offline-icon ps-offline-icon--cash">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                </div>
                <div>
                    <div class="ps-offline-label">{{ __('Cash') }}</div>
                    <div class="ps-offline-hint">{{ __('Record cash payments at point of sale') }}</div>
                </div>
            </label>
            <label class="ps-offline-item">
                <input wire:model="offlineCardEnabled" type="checkbox" class="ps-checkbox">
                <div class="ps-offline-icon ps-offline-icon--card">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div>
                    <div class="ps-offline-label">{{ __('Card terminal') }}</div>
                    <div class="ps-offline-hint">{{ __('Record card payments via physical terminal') }}</div>
                </div>
            </label>
            <label class="ps-offline-item">
                <input wire:model="offlineBankTransferEnabled" type="checkbox" class="ps-checkbox">
                <div class="ps-offline-icon ps-offline-icon--bank">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg>
                </div>
                <div>
                    <div class="ps-offline-label">{{ __('Bank transfer') }}</div>
                    <div class="ps-offline-hint">{{ __('Record direct bank / wire transfer payments') }}</div>
                </div>
            </label>
        </div>
    </div>

    {{-- ── Save button ────────────────────────────────────────────────── --}}
    <div>
        <x-filament::button wire:click="save" wire:loading.attr="disabled">
            {{ __('Save Changes') }}
        </x-filament::button>
    </div>

</div>

</x-filament-panels::page>
