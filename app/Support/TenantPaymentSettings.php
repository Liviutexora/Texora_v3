<?php

namespace App\Support;

use App\Models\Setting;

class TenantPaymentSettings
{
    public const TOKEN_PLACEHOLDER = '••••••••';

    public const GATEWAYS = ['stripe', 'razorpay', 'paypal', 'paddle'];

    public function __construct(public readonly int $tenantId) {}

    public static function for(int $tenantId): self
    {
        return new self($tenantId);
    }

    private function key(string $suffix): string
    {
        return "tenant_{$this->tenantId}_{$suffix}";
    }

    public function isEnabled(): bool
    {
        return (bool) Setting::get($this->key('payment_enabled'), false);
    }

    public function requirePaymentAtBooking(): bool
    {
        return (bool) Setting::get($this->key('require_payment_at_booking'), false);
    }

    public function defaultGateway(): string
    {
        return (string) Setting::get($this->key('default_gateway'), 'stripe');
    }

    public function offlineCashEnabled(): bool
    {
        return (bool) Setting::get($this->key('offline_cash_enabled'), true);
    }

    public function offlineCardEnabled(): bool
    {
        return (bool) Setting::get($this->key('offline_card_enabled'), true);
    }

    public function offlineBankTransferEnabled(): bool
    {
        return (bool) Setting::get($this->key('offline_bank_transfer_enabled'), true);
    }

    public function stripePublishableKey(): ?string
    {
        $v = Setting::get($this->key('stripe_publishable_key'));

        return $v ?: null;
    }

    public function stripeSecretKey(): ?string
    {
        return $this->decryptSecret('stripe_secret_key');
    }

    public function stripeWebhookSecret(): ?string
    {
        return $this->decryptSecret('stripe_webhook_secret');
    }

    public function razorpayKeyId(): ?string
    {
        $v = Setting::get($this->key('razorpay_key_id'));

        return $v ?: null;
    }

    public function razorpayKeySecret(): ?string
    {
        return $this->decryptSecret('razorpay_key_secret');
    }

    public function razorpayWebhookSecret(): ?string
    {
        return $this->decryptSecret('razorpay_webhook_secret');
    }

    public function paypalClientId(): ?string
    {
        $v = Setting::get($this->key('paypal_client_id'));

        return $v ?: null;
    }

    public function paypalClientSecret(): ?string
    {
        return $this->decryptSecret('paypal_client_secret');
    }

    public function paypalMode(): string
    {
        return (string) Setting::get($this->key('paypal_mode'), 'sandbox');
    }

    public function paddleVendorId(): ?string
    {
        $v = Setting::get($this->key('paddle_vendor_id'));

        return $v ?: null;
    }

    public function paddleApiKey(): ?string
    {
        return $this->decryptSecret('paddle_api_key');
    }

    public function paddleWebhookSecret(): ?string
    {
        return $this->decryptSecret('paddle_webhook_secret');
    }

    public function isGatewayConfigured(string $gateway): bool
    {
        return match ($gateway) {
            'stripe'   => $this->stripeSecretKey() && $this->stripePublishableKey(),
            'razorpay' => $this->razorpayKeyId() && $this->razorpayKeySecret(),
            'paypal'   => $this->paypalClientId() && $this->paypalClientSecret(),
            'paddle'   => $this->paddleVendorId() && $this->paddleApiKey(),
            default    => false,
        };
    }

    public function activeGateway(): ?string
    {
        $preferred = $this->defaultGateway();
        if ($this->isGatewayConfigured($preferred)) {
            return $preferred;
        }

        foreach (self::GATEWAYS as $gateway) {
            if ($this->isGatewayConfigured($gateway)) {
                return $gateway;
            }
        }

        return null;
    }

    private function decryptSecret(string $suffix): ?string
    {
        $value = Setting::get($this->key($suffix));
        if (! $value) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function hasSecret(string $suffix): bool
    {
        return (bool) Setting::get($this->key($suffix));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(array $data): void
    {
        Setting::set($this->key('payment_enabled'), ($data['payment_enabled'] ?? false) ? '1' : '0');
        Setting::set($this->key('require_payment_at_booking'), ($data['require_payment_at_booking'] ?? false) ? '1' : '0');
        Setting::set($this->key('default_gateway'), $data['default_gateway'] ?? 'stripe');
        Setting::set($this->key('offline_cash_enabled'), ($data['offline_cash_enabled'] ?? true) ? '1' : '0');
        Setting::set($this->key('offline_card_enabled'), ($data['offline_card_enabled'] ?? true) ? '1' : '0');
        Setting::set($this->key('offline_bank_transfer_enabled'), ($data['offline_bank_transfer_enabled'] ?? true) ? '1' : '0');

        Setting::set($this->key('stripe_publishable_key'), $data['stripe_publishable_key'] ?? '');
        $this->saveSecret('stripe_secret_key', $data['stripe_secret_key'] ?? null);
        $this->saveSecret('stripe_webhook_secret', $data['stripe_webhook_secret'] ?? null);

        Setting::set($this->key('razorpay_key_id'), $data['razorpay_key_id'] ?? '');
        $this->saveSecret('razorpay_key_secret', $data['razorpay_key_secret'] ?? null);
        $this->saveSecret('razorpay_webhook_secret', $data['razorpay_webhook_secret'] ?? null);

        Setting::set($this->key('paypal_client_id'), $data['paypal_client_id'] ?? '');
        $this->saveSecret('paypal_client_secret', $data['paypal_client_secret'] ?? null);
        Setting::set($this->key('paypal_mode'), $data['paypal_mode'] ?? 'sandbox');

        Setting::set($this->key('paddle_vendor_id'), $data['paddle_vendor_id'] ?? '');
        $this->saveSecret('paddle_api_key', $data['paddle_api_key'] ?? null);
        $this->saveSecret('paddle_webhook_secret', $data['paddle_webhook_secret'] ?? null);
    }

    private function saveSecret(string $suffix, ?string $value): void
    {
        if ($value && $value !== self::TOKEN_PLACEHOLDER) {
            Setting::set($this->key($suffix), encrypt($value));
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toFormData(): array
    {
        return [
            'payment_enabled'              => $this->isEnabled(),
            'require_payment_at_booking'     => $this->requirePaymentAtBooking(),
            'default_gateway'                => $this->defaultGateway(),
            'offline_cash_enabled'         => $this->offlineCashEnabled(),
            'offline_card_enabled'           => $this->offlineCardEnabled(),
            'offline_bank_transfer_enabled'  => $this->offlineBankTransferEnabled(),
            'stripe_publishable_key'       => $this->stripePublishableKey() ?? '',
            'stripe_secret_key'            => $this->hasSecret('stripe_secret_key') ? self::TOKEN_PLACEHOLDER : '',
            'stripe_webhook_secret'        => $this->hasSecret('stripe_webhook_secret') ? self::TOKEN_PLACEHOLDER : '',
            'razorpay_key_id'              => $this->razorpayKeyId() ?? '',
            'razorpay_key_secret'          => $this->hasSecret('razorpay_key_secret') ? self::TOKEN_PLACEHOLDER : '',
            'razorpay_webhook_secret'      => $this->hasSecret('razorpay_webhook_secret') ? self::TOKEN_PLACEHOLDER : '',
            'paypal_client_id'             => $this->paypalClientId() ?? '',
            'paypal_client_secret'         => $this->hasSecret('paypal_client_secret') ? self::TOKEN_PLACEHOLDER : '',
            'paypal_mode'                  => $this->paypalMode(),
            'paddle_vendor_id'             => $this->paddleVendorId() ?? '',
            'paddle_api_key'               => $this->hasSecret('paddle_api_key') ? self::TOKEN_PLACEHOLDER : '',
            'paddle_webhook_secret'        => $this->hasSecret('paddle_webhook_secret') ? self::TOKEN_PLACEHOLDER : '',
        ];
    }
}
