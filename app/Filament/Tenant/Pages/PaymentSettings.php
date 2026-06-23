<?php

namespace App\Filament\Tenant\Pages;

use App\Helpers\DemoModeHelper;
use App\Support\TenantContext;
use App\Support\TenantPaymentSettings;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PaymentSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 15;

    protected static ?string $slug = 'payment-settings';

    protected string $view = 'filament.tenant.pages.payment-settings';

    // ── General ────────────────────────────────────────────────────────────
    public bool   $paymentEnabled          = false;
    public bool   $requirePaymentAtBooking = false;
    public string $activeGateway           = 'stripe';

    // ── Offline methods ────────────────────────────────────────────────────
    public bool $offlineCashEnabled         = true;
    public bool $offlineCardEnabled         = true;
    public bool $offlineBankTransferEnabled = true;

    // ── Stripe ─────────────────────────────────────────────────────────────
    public string $stripePublishableKey = '';
    public string $stripeSecretKey      = '';
    public string $stripeWebhookSecret  = '';

    // ── Razorpay ───────────────────────────────────────────────────────────
    public string $razorpayKeyId         = '';
    public string $razorpayKeySecret     = '';
    public string $razorpayWebhookSecret = '';

    // ── PayPal ─────────────────────────────────────────────────────────────
    public string $paypalClientId     = '';
    public string $paypalClientSecret = '';
    public string $paypalMode         = 'sandbox';

    // ── Paddle ─────────────────────────────────────────────────────────────
    public string $paddleVendorId       = '';
    public string $paddleApiKey         = '';
    public string $paddleWebhookSecret  = '';

    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Integrations');
    }

    public static function getNavigationLabel(): string
    {
        return __('Payments');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Payment Settings');
    }

    public function mount(): void
    {
        $tenant = TenantContext::current();
        if (! $tenant) {
            return;
        }

        $ps = TenantPaymentSettings::for($tenant->id);

        $this->paymentEnabled          = $ps->isEnabled();
        $this->requirePaymentAtBooking = $ps->requirePaymentAtBooking();
        $this->activeGateway           = $ps->defaultGateway() ?: 'stripe';

        $this->offlineCashEnabled         = $ps->offlineCashEnabled();
        $this->offlineCardEnabled         = $ps->offlineCardEnabled();
        $this->offlineBankTransferEnabled = $ps->offlineBankTransferEnabled();

        $this->stripePublishableKey = $ps->stripePublishableKey() ?? '';
        $this->stripeSecretKey      = $ps->stripeSecretKey()      ? TenantPaymentSettings::TOKEN_PLACEHOLDER : '';
        $this->stripeWebhookSecret  = $ps->stripeWebhookSecret()  ? TenantPaymentSettings::TOKEN_PLACEHOLDER : '';

        $this->razorpayKeyId         = $ps->razorpayKeyId()         ?? '';
        $this->razorpayKeySecret     = $ps->razorpayKeySecret()     ? TenantPaymentSettings::TOKEN_PLACEHOLDER : '';
        $this->razorpayWebhookSecret = $ps->razorpayWebhookSecret() ? TenantPaymentSettings::TOKEN_PLACEHOLDER : '';

        $this->paypalClientId     = $ps->paypalClientId()     ?? '';
        $this->paypalClientSecret = $ps->paypalClientSecret() ? TenantPaymentSettings::TOKEN_PLACEHOLDER : '';
        $this->paypalMode         = $ps->paypalMode();

        $this->paddleVendorId      = $ps->paddleVendorId()      ?? '';
        $this->paddleApiKey        = $ps->paddleApiKey()        ? TenantPaymentSettings::TOKEN_PLACEHOLDER : '';
        $this->paddleWebhookSecret = $ps->paddleWebhookSecret() ? TenantPaymentSettings::TOKEN_PLACEHOLDER : '';
    }

    public function selectGateway(string $gateway): void
    {
        if (in_array($gateway, TenantPaymentSettings::GATEWAYS, true)) {
            $this->activeGateway = $gateway;
        }
    }

    public function save(): void
    {
        if (DemoModeHelper::isEnabled()) {
            Notification::make()->title(__('Demo Mode'))->body(DemoModeHelper::getRestrictedMessage())->warning()->send();
            return;
        }

        $tenant = TenantContext::current();
        if (! $tenant) {
            return;
        }

        TenantPaymentSettings::for($tenant->id)->save([
            'payment_enabled'              => $this->paymentEnabled,
            'require_payment_at_booking'   => $this->requirePaymentAtBooking,
            'default_gateway'              => $this->activeGateway,
            'offline_cash_enabled'         => $this->offlineCashEnabled,
            'offline_card_enabled'         => $this->offlineCardEnabled,
            'offline_bank_transfer_enabled'=> $this->offlineBankTransferEnabled,
            'stripe_publishable_key'       => $this->stripePublishableKey,
            'stripe_secret_key'            => $this->stripeSecretKey,
            'stripe_webhook_secret'        => $this->stripeWebhookSecret,
            'razorpay_key_id'              => $this->razorpayKeyId,
            'razorpay_key_secret'          => $this->razorpayKeySecret,
            'razorpay_webhook_secret'      => $this->razorpayWebhookSecret,
            'paypal_client_id'             => $this->paypalClientId,
            'paypal_client_secret'         => $this->paypalClientSecret,
            'paypal_mode'                  => $this->paypalMode,
            'paddle_vendor_id'             => $this->paddleVendorId,
            'paddle_api_key'               => $this->paddleApiKey,
            'paddle_webhook_secret'        => $this->paddleWebhookSecret,
        ]);

        // Re-mask secrets so the form doesn't expose live values after save
        $ps = TenantPaymentSettings::for($tenant->id);
        if ($ps->stripeSecretKey())      $this->stripeSecretKey      = TenantPaymentSettings::TOKEN_PLACEHOLDER;
        if ($ps->stripeWebhookSecret())  $this->stripeWebhookSecret  = TenantPaymentSettings::TOKEN_PLACEHOLDER;
        if ($ps->razorpayKeySecret())    $this->razorpayKeySecret    = TenantPaymentSettings::TOKEN_PLACEHOLDER;
        if ($ps->razorpayWebhookSecret()) $this->razorpayWebhookSecret = TenantPaymentSettings::TOKEN_PLACEHOLDER;
        if ($ps->paypalClientSecret())   $this->paypalClientSecret   = TenantPaymentSettings::TOKEN_PLACEHOLDER;
        if ($ps->paddleApiKey())         $this->paddleApiKey         = TenantPaymentSettings::TOKEN_PLACEHOLDER;
        if ($ps->paddleWebhookSecret())  $this->paddleWebhookSecret  = TenantPaymentSettings::TOKEN_PLACEHOLDER;

        Notification::make()->title(__('Payment settings saved'))->success()->send();
    }
}
