<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Models\NotificationPreference;
use App\Models\Setting;
use App\Models\SlotReservation;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Services\BookingPaymentService;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $secret  = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Verify webhook signature when secret is configured
        if ($secret) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $secret);
            } catch (SignatureVerificationException $e) {
                Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

                return response('Invalid signature', 400);
            } catch (\UnexpectedValueException $e) {
                return response('Invalid payload', 400);
            }
        } else {
            // No secret — events are unverified. Safe for local dev; dangerous in production.
            Log::critical('Stripe webhook received without signature verification — set STRIPE_WEBHOOK_SECRET to secure this endpoint');
            $data  = json_decode($payload, true);
            $event = (object) ['type' => $data['type'] ?? '', 'data' => (object) ['object' => (object) ($data['data']['object'] ?? [])]];
        }

        match ($event->type) {
            'checkout.session.completed'              => $this->handleCheckoutSessionCompleted($event->data->object),
            'customer.subscription.updated'           => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted'           => $this->handleSubscriptionDeleted($event->data->object),
            'customer.subscription.trial_will_end'    => $this->handleTrialWillEnd($event->data->object),
            'invoice.payment_failed'                  => $this->handlePaymentFailed($event->data->object),
            'invoice.payment_succeeded'               => $this->handlePaymentSucceeded($event->data->object),
            default                                   => null,
        };

        return response('OK', 200);
    }

    /**
     * checkout.session.completed — fires when a Stripe Checkout session succeeds.
     *
     * Critical for Free→Paid upgrades from /manage/billing: at this point the tenant
     * record doesn't yet have stripe_customer_id / stripe_subscription_id because the
     * registration flow only handled the initial signup. We write these here using
     * client_reference_id (set to tenant_id when creating the Checkout session in
     * BillingService::createCheckoutSession).
     */
    private function handleCheckoutSessionCompleted(object $session): void
    {
        if (($session->mode ?? '') === 'payment') {
            $metadata = $session->metadata ?? [];
            if (is_object($metadata)) {
                $metadata = (array) $metadata;
            }
            if (($metadata['type'] ?? '') === 'booking_payment') {
                $bookingId = $metadata['booking_id'] ?? $session->client_reference_id ?? null;
                if ($bookingId) {
                    $booking = SlotReservation::withoutGlobalScope('tenant')->find($bookingId);
                    if ($booking && $booking->payment_status !== 'paid') {
                        app(BookingPaymentService::class)->markPaid(
                            $booking,
                            'stripe',
                            $session->payment_intent ?? $session->id ?? null,
                            $session->id ?? null,
                        );
                    }
                }
            }

            return;
        }

        // Only act on subscription-mode checkout sessions
        if (($session->mode ?? '') !== 'subscription') {
            return;
        }

        $tenantId = $session->client_reference_id ?? null;
        if (! $tenantId) {
            // Could be from the registration flow — safe to ignore
            return;
        }

        $tenant = Tenant::find($tenantId);
        if (! $tenant) {
            Log::warning('Stripe webhook: checkout.session.completed — tenant not found', [
                'client_reference_id' => $tenantId,
                'session_id'          => $session->id,
            ]);

            return;
        }

        $customerId     = $session->customer ?? null;
        $subscriptionId = $session->subscription ?? null;

        if (! $customerId || ! $subscriptionId) {
            Log::warning('Stripe webhook: checkout.session.completed — missing customer/subscription', [
                'tenant_id'  => $tenant->id,
                'session_id' => $session->id,
            ]);

            return;
        }

        $updates = [
            'stripe_customer_id'     => $customerId,
            'stripe_subscription_id' => $subscriptionId,
        ];

        // Mark subscription as active if status is not yet set
        // (customer.subscription.updated may arrive shortly after to refine this)
        if (empty($tenant->stripe_subscription_status) || $tenant->stripe_subscription_status === 'canceled') {
            $updates['stripe_subscription_status'] = 'active';
            $updates['status'] = 'active';
        }

        // Set plan_id immediately from session metadata — don't wait for the subscription.updated
        // event which may arrive out of order or not at all on retry
        $planIdFromMetadata = $session->metadata->plan_id ?? null;
        if ($planIdFromMetadata) {
            $updates['plan_id'] = (int) $planIdFromMetadata;
        }

        $tenant->update($updates);

        Log::info('Stripe webhook: checkout.session.completed — tenant billing IDs written', [
            'tenant_id'       => $tenant->id,
            'customer_id'     => $customerId,
            'subscription_id' => $subscriptionId,
            'plan_id'         => $planIdFromMetadata,
        ]);
    }

    private function handleSubscriptionUpdated(object $subscription): void
    {
        $tenant = Tenant::where('stripe_subscription_id', $subscription->id)->first();
        if (! $tenant) {
            Log::info('Stripe webhook: subscription updated for unknown tenant', ['sub_id' => $subscription->id]);

            return;
        }

        $newStatus = $subscription->status; // trialing | active | past_due | canceled | unpaid | paused

        $updates = [
            'stripe_subscription_status' => $newStatus,
            'status' => in_array($newStatus, ['active', 'trialing', 'past_due']) ? 'active' : 'suspended',
        ];

        // Update plan_id if the subscription's price maps to a known plan.
        // Look up in subscription_plan_prices first (new normalized table),
        // then fall back to the legacy stripe_price_id column on subscription_plans.
        $priceId = $subscription->items->data[0]->price->id ?? null;
        if ($priceId) {
            $planPrice = \App\Models\SubscriptionPlanPrice::where('stripe_price_id', $priceId)->first();
            $plan = $planPrice?->plan ?? SubscriptionPlan::where('stripe_price_id', $priceId)->first();

            if ($plan) {
                $updates['plan_id'] = $plan->id;
            } else {
                Log::warning('Stripe webhook: subscription.updated — no plan found for price ID', [
                    'tenant_id' => $tenant->id,
                    'price_id'  => $priceId,
                ]);
                // Do NOT null plan_id — leave it unchanged
            }
        }

        $tenant->update($updates);

        Log::info('Stripe webhook: subscription status updated', [
            'tenant_id' => $tenant->id,
            'status'    => $newStatus,
        ]);
    }

    private function handleSubscriptionDeleted(object $subscription): void
    {
        $tenant = Tenant::with('owner')->where('stripe_subscription_id', $subscription->id)->first();
        if (! $tenant) {
            return;
        }

        $tenant->update([
            'stripe_subscription_status' => 'canceled',
            'status'                     => 'suspended',
        ]);

        Log::info('Stripe webhook: subscription cancelled — tenant suspended', ['tenant_id' => $tenant->id]);

        $owner = $tenant->owner;
        if (! $owner?->email) {
            return;
        }

        $siteName   = Setting::get('site_name', config('app.name', 'Slotara'));
        $billingUrl = url('/manage/billing');

        $placeholders = [
            'OWNER_NAME'    => $owner->name,
            'BUSINESS_NAME' => $tenant->name,
            'SITE_NAME'     => $siteName,
            'BILLING_URL'   => $billingUrl,
        ];

        $fallbackBody = <<<HTML
<p>Hi <strong>{$owner->name}</strong>,</p>
<p>Your subscription for <strong>{$tenant->name}</strong> on {$siteName} has been cancelled and your account has been suspended.</p>
<p>Your booking page is no longer accepting new appointments. You can resubscribe at any time from your billing page.</p>
<p style="margin:24px 0;">
    <a href="{$billingUrl}" style="display:inline-block;padding:12px 24px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Resubscribe Now</a>
</p>
HTML;

        try {
            EmailTemplateService::sendWithLayoutFallback(
                to: $owner->email,
                subjectFallback: "Your {$siteName} subscription has been cancelled",
                bodyFallback: $fallbackBody,
                placeholders: $placeholders,
                templateSlug: 'subscription_cancelled',
            );

            Log::info('Stripe webhook: subscription_cancelled email sent', ['tenant_id' => $tenant->id]);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook: failed to send subscription_cancelled email', [
                'tenant_id' => $tenant->id,
                'error'     => $e->getMessage(),
            ]);
        }

        // Notify super-admins who opted in to subscription_cancelled
        $adminPrefs = NotificationPreference::where('permission_name', 'subscription_cancelled')
            ->where(function ($q) {
                $q->where('email', true)->orWhere('web_notification', true);
            })
            ->with('user')
            ->get();

        $tenantUrl = rescue(fn () => route('filament.admin.resources.tenants.edit', ['record' => $tenant->id]), null);

        foreach ($adminPrefs as $pref) {
            if ($pref->web_notification) {
                NotificationHelper::send(
                    receiverId: $pref->user_id,
                    heading: 'Tenant Subscription Cancelled',
                    message: "{$tenant->name} has cancelled their subscription. Account suspended.",
                    url: $tenantUrl,
                );
            }
            if ($pref->email && $pref->user?->email) {
                try {
                    EmailTemplateService::sendWithLayoutFallback(
                        to: $pref->user->email,
                        subjectFallback: "Subscription cancelled — {$tenant->name}",
                        bodyFallback: "<p>Hi <strong>{$pref->user->name}</strong>,</p>"
                            . "<p>The subscription for tenant <strong>{$tenant->name}</strong> has been <strong>cancelled</strong>. Their account is now suspended.</p>"
                            . "<p><a href=\"" . url('/admin/tenants/' . $tenant->id . '/edit') . "\" "
                            . "style=\"display:inline-block;padding:10px 20px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;\">View Tenant</a></p>",
                        placeholders: [
                            'OWNER_NAME'    => $pref->user->name,
                            'BUSINESS_NAME' => $tenant->name,
                            'SITE_NAME'     => $siteName,
                        ],
                    );
                } catch (\Throwable $e) {
                    Log::error('Stripe webhook: failed to send subscription_cancelled admin notification', [
                        'admin_id'  => $pref->user_id,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    private function handlePaymentFailed(object $invoice): void
    {
        $subscriptionId = $invoice->subscription ?? null;
        if (! $subscriptionId) {
            return;
        }

        $tenant = Tenant::with('owner')->where('stripe_subscription_id', $subscriptionId)->first();
        if (! $tenant) {
            return;
        }

        $tenant->update(['stripe_subscription_status' => 'past_due']);

        Log::warning('Stripe webhook: payment failed — tenant marked past_due', ['tenant_id' => $tenant->id]);

        $owner = $tenant->owner;
        if (! $owner?->email) {
            return;
        }

        $siteName   = Setting::get('site_name', config('app.name', 'Slotara'));
        $billingUrl = url('/manage/billing');

        $placeholders = [
            'OWNER_NAME'    => $owner->name,
            'BUSINESS_NAME' => $tenant->name,
            'SITE_NAME'     => $siteName,
            'BILLING_URL'   => $billingUrl,
        ];

        $fallbackBody = <<<HTML
<p>Hi <strong>{$owner->name}</strong>,</p>
<p>We were unable to process the latest payment for your <strong>{$tenant->name}</strong> subscription on {$siteName}.</p>
<p>Your account is now marked as <strong>past due</strong>. Please update your payment method to avoid service interruption.</p>
<p style="margin:24px 0;">
    <a href="{$billingUrl}" style="display:inline-block;padding:12px 24px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Update Payment Method</a>
</p>
HTML;

        try {
            EmailTemplateService::sendWithLayoutFallback(
                to: $owner->email,
                subjectFallback: "Action required: payment failed for {$tenant->name}",
                bodyFallback: $fallbackBody,
                placeholders: $placeholders,
                templateSlug: 'payment_failed',
            );

            Log::info('Stripe webhook: payment_failed email sent', ['tenant_id' => $tenant->id]);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook: failed to send payment_failed email', [
                'tenant_id' => $tenant->id,
                'error'     => $e->getMessage(),
            ]);
        }

        // Notify super-admins who opted in to payment_failed
        $adminPrefs = NotificationPreference::where('permission_name', 'payment_failed')
            ->where(function ($q) {
                $q->where('email', true)->orWhere('web_notification', true);
            })
            ->with('user')
            ->get();

        $tenantUrl = rescue(fn () => route('filament.admin.resources.tenants.edit', ['record' => $tenant->id]), null);

        foreach ($adminPrefs as $pref) {
            if ($pref->web_notification) {
                NotificationHelper::send(
                    receiverId: $pref->user_id,
                    heading: 'Tenant Payment Failed',
                    message: "Payment failed for {$tenant->name}. Account marked past due.",
                    url: $tenantUrl,
                );
            }
            if ($pref->email && $pref->user?->email) {
                try {
                    EmailTemplateService::sendWithLayoutFallback(
                        to: $pref->user->email,
                        subjectFallback: "Payment failed — {$tenant->name}",
                        bodyFallback: "<p>Hi <strong>{$pref->user->name}</strong>,</p>"
                            . "<p>A payment failure was detected for tenant <strong>{$tenant->name}</strong>.</p>"
                            . "<p>Their account is now marked as <strong>past due</strong>.</p>"
                            . "<p><a href=\"" . url('/admin/tenants/' . $tenant->id . '/edit') . "\" "
                            . "style=\"display:inline-block;padding:10px 20px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;\">View Tenant</a></p>",
                        placeholders: [
                            'OWNER_NAME'    => $pref->user->name,
                            'BUSINESS_NAME' => $tenant->name,
                            'SITE_NAME'     => $siteName,
                        ],
                    );
                } catch (\Throwable $e) {
                    Log::error('Stripe webhook: failed to send payment_failed admin notification', [
                        'admin_id'  => $pref->user_id,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * customer.subscription.trial_will_end — fires 3 days before a trial ends.
     * Sends a warning email to the tenant owner so they can add a payment method.
     */
    private function handleTrialWillEnd(object $subscription): void
    {
        $tenant = Tenant::with('owner')->where('stripe_subscription_id', $subscription->id)->first();
        if (! $tenant) {
            return;
        }

        $owner = $tenant->owner;
        if (! $owner?->email) {
            return;
        }

        $siteName   = Setting::get('site_name', config('app.name', 'Slotara'));
        $billingUrl = url('/manage/billing');
        $trialEnd   = $subscription->trial_end
            ? \Carbon\Carbon::createFromTimestamp($subscription->trial_end)->format('d M Y')
            : 'soon';

        $placeholders = [
            'OWNER_NAME'    => $owner->name,
            'BUSINESS_NAME' => $tenant->name,
            'SITE_NAME'     => $siteName,
            'BILLING_URL'   => $billingUrl,
            'TRIAL_END'     => $trialEnd,
        ];

        $fallbackBody = <<<HTML
<p>Hi <strong>{$owner->name}</strong>,</p>
<p>Your free trial for <strong>{$tenant->name}</strong> on {$siteName} ends on <strong>{$trialEnd}</strong>.</p>
<p>To keep your booking page live, please add a payment method before your trial expires.</p>
<p style="margin:24px 0;">
    <a href="{$billingUrl}" style="display:inline-block;padding:12px 24px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Add Payment Method</a>
</p>
HTML;

        try {
            EmailTemplateService::sendWithLayoutFallback(
                to: $owner->email,
                subjectFallback: "Your {$siteName} trial ends on {$trialEnd}",
                bodyFallback: $fallbackBody,
                placeholders: $placeholders,
                templateSlug: 'trial_ending',
            );

            Log::info('Stripe webhook: trial_will_end email sent', ['tenant_id' => $tenant->id]);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook: failed to send trial_will_end email', [
                'tenant_id' => $tenant->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    private function handlePaymentSucceeded(object $invoice): void
    {
        $subscriptionId = $invoice->subscription ?? null;
        if (! $subscriptionId) {
            return;
        }

        $tenant = Tenant::where('stripe_subscription_id', $subscriptionId)->first();
        if (! $tenant) {
            return;
        }

        if ($tenant->stripe_subscription_status === 'past_due') {
            $tenant->update([
                'stripe_subscription_status' => 'active',
                'status'                     => 'active',
            ]);

            Log::info('Stripe webhook: payment recovered — tenant restored to active', ['tenant_id' => $tenant->id]);
        }
    }

}
