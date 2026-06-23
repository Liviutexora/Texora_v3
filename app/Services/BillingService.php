<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanPrice;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Invoice;
use Stripe\Stripe;

class BillingService
{
    /**
     * Create a Stripe Billing Portal session URL and return it.
     * Returns null (not an exception) on failure so callers can show a
     * friendly message instead of a 500.
     */
    public function createPortalSession(Tenant $tenant, string $returnUrl): ?string
    {
        if (! $tenant->stripe_customer_id) {
            Log::warning('BillingService: portal session requested but tenant has no stripe_customer_id', [
                'tenant_id' => $tenant->id,
            ]);

            return null;
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $params = [
                'customer'   => $tenant->stripe_customer_id,
                'return_url' => $returnUrl,
            ];

            // Use portal configuration ID if configured — required for plan switching
            $portalConfigId = Setting::get('stripe_portal_config_id');
            if ($portalConfigId) {
                $params['configuration'] = $portalConfigId;
            }

            $session = PortalSession::create($params);

            return $session->url;
        } catch (\Throwable $e) {
            Log::error('BillingService: failed to create Stripe portal session', [
                'tenant_id' => $tenant->id,
                'error'     => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a Stripe Checkout session for upgrading from Free → Paid.
     *
     * @param SubscriptionPlanPrice $planPrice  The specific billing cycle price to charge.
     * Passes tenant_id as client_reference_id so the webhook can write
     * stripe_customer_id + stripe_subscription_id on checkout.session.completed.
     */
    public function createCheckoutSession(
        Tenant $tenant,
        SubscriptionPlanPrice $planPrice,
        string $successUrl,
        string $cancelUrl
    ): ?string {
        if (! $planPrice->stripe_price_id) {
            Log::error('BillingService: plan price has no stripe_price_id', [
                'plan_id'   => $planPrice->plan_id,
                'cycle'     => $planPrice->billing_cycle,
            ]);

            return null;
        }

        $plan = $planPrice->plan;

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $params = [
                'mode'                 => 'subscription',
                'payment_method_types' => ['card'],
                'line_items'           => [[
                    'price'    => $planPrice->stripe_price_id,
                    'quantity' => 1,
                ]],
                'subscription_data'    => [
                    'metadata' => [
                        'tenant_id'     => $tenant->id,
                        'plan_id'       => $plan->id,
                        'plan_name'     => $plan->name,
                        'billing_cycle' => $planPrice->billing_cycle,
                    ],
                ],
                'client_reference_id' => (string) $tenant->id,
                'success_url'         => $successUrl,
                'cancel_url'          => $cancelUrl,
                'metadata'            => [
                    'tenant_id' => $tenant->id,
                    'plan_id'   => $plan->id,
                ],
            ];

            if ($tenant->stripe_customer_id) {
                $params['customer'] = $tenant->stripe_customer_id;
            } elseif ($tenant->owner?->email) {
                $params['customer_email'] = $tenant->owner->email;
            }

            $session = CheckoutSession::create($params);

            return $session->url;
        } catch (\Throwable $e) {
            Log::error('BillingService: failed to create Stripe checkout session', [
                'tenant_id' => $tenant->id,
                'plan_id'   => $plan->id,
                'cycle'     => $planPrice->billing_cycle,
                'error'     => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Fetch the last N invoices for a tenant, cached for 5 minutes.
     * Returns an empty array if no stripe_customer_id or on Stripe error.
     */
    public function getRecentInvoices(Tenant $tenant, int $limit = 10): array
    {
        if (! $tenant->stripe_customer_id) {
            return [];
        }

        $cacheKey = "invoices.tenant_{$tenant->id}";

        return Cache::remember($cacheKey, 300, function () use ($tenant, $limit) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));

                $invoices = Invoice::all([
                    'customer' => $tenant->stripe_customer_id,
                    'limit'    => $limit,
                ]);

                return array_map(function ($inv) {
                    return [
                        'id'          => $inv->id,
                        'number'      => $inv->number,
                        'status'      => $inv->status,
                        'amount'      => $inv->amount_paid / 100,
                        'currency'    => strtoupper($inv->currency),
                        'created'     => Carbon::createFromTimestamp($inv->created)->format('d M Y'),
                        'hosted_url'  => $inv->hosted_invoice_url,
                        'pdf_url'     => $inv->invoice_pdf,
                    ];
                }, $invoices->data ?? []);
            } catch (\Throwable $e) {
                Log::warning('BillingService: failed to fetch invoices', [
                    'tenant_id' => $tenant->id,
                    'error'     => $e->getMessage(),
                ]);

                return [];
            }
        });
    }

    /**
     * Bust the invoice cache for a tenant (call after returning from Stripe).
     */
    public function bustInvoiceCache(Tenant $tenant): void
    {
        Cache::forget("invoices.tenant_{$tenant->id}");
    }

    /**
     * Return trial end date for a tenant from the local column (no Stripe API call).
     * Returns null if no trial or trial has already ended.
     */
    public function trialEndsAt(Tenant $tenant): ?Carbon
    {
        if (! $tenant->trial_ends_at) {
            return null;
        }

        $trialEnd = Carbon::parse($tenant->trial_ends_at);

        return $trialEnd->isFuture() ? $trialEnd : null;
    }

    /**
     * Determine the billing state for a tenant.
     * Returns: 'free' | 'checkout_only' | 'active' | 'manual'
     *
     * - 'free'          → no stripe_customer_id and no paid plan; show Checkout upgrade
     * - 'checkout_only' → has Stripe customer but subscription null/canceled; show Checkout
     * - 'active'        → trialing/active/past_due/paused via Stripe; show portal controls
     * - 'manual'        → plan assigned by admin without Stripe (no customer ID); show plan
     *                     info only — no portal (nothing to portal to), no upgrade CTA
     */
    public function billingState(Tenant $tenant): string
    {
        if (! $tenant->stripe_customer_id) {
            // Tenant is manually active with a paid plan — admin-assigned, no Stripe needed
            if ($tenant->status === 'active' && $tenant->plan_id) {
                return 'manual';
            }

            return 'free';
        }

        $activeStatuses = ['trialing', 'active', 'past_due', 'paused'];

        if (in_array($tenant->stripe_subscription_status, $activeStatuses)) {
            return 'active';
        }

        return 'checkout_only';
    }

    /**
     * Check whether the Stripe portal configuration ID has been set by the admin.
     * Portal actions (cancel/reactivate/update card/downgrade) require this.
     */
    public function isPortalConfigured(): bool
    {
        return (bool) Setting::get('stripe_portal_config_id');
    }
}
