<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Services\BillingService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;

class BillingController extends Controller
{
    public function __construct(private readonly BillingService $billing) {}

    /**
     * Redirect to Stripe Billing Portal (cancel / reactivate / update payment / downgrade).
     */
    public function portal(): RedirectResponse
    {
        $tenant = TenantContext::current();

        if (! $tenant) {
            return redirect()->route('filament.tenant.pages.billing')
                ->with('billing_error', __('Unable to determine your account. Please try again.'));
        }

        $returnUrl = url('/manage/billing?session=done');
        $url       = $this->billing->createPortalSession($tenant, $returnUrl);

        if (! $url) {
            $errorMessage = ! $tenant->stripe_customer_id
                ? __('No active subscription found. Please choose a plan to get started.')
                : __('Unable to open the billing portal right now. Please try again in a moment.');

            return redirect()->route('filament.tenant.pages.billing')
                ->with('billing_error', $errorMessage);
        }

        return redirect()->away($url);
    }

    /**
     * Create a Stripe Checkout session for a specific plan + billing cycle.
     * POST /manage/billing/checkout/{plan:slug}/{cycle}
     */
    public function checkout(SubscriptionPlan $plan, string $cycle): RedirectResponse
    {
        $tenant = TenantContext::current();

        if (! $tenant) {
            return redirect()->route('filament.tenant.pages.billing')
                ->with('billing_error', __('Unable to determine your account. Please try again.'));
        }

        // Prevent creating a second subscription when one is already active
        $activeStatuses = ['trialing', 'active', 'past_due', 'paused'];
        if (in_array($tenant->stripe_subscription_status, $activeStatuses)) {
            return redirect()->route('filament.tenant.pages.billing')
                ->with('billing_error', __('You already have an active subscription. Use the billing portal to make changes.'));
        }

        if (! $plan->is_active) {
            return redirect()->route('filament.tenant.pages.billing')
                ->with('billing_error', __('This plan is no longer available.'));
        }

        // Find the price for the requested cycle
        $planPrice = $plan->prices()
            ->where('billing_cycle', $cycle)
            ->where('is_active', true)
            ->first();

        if (! $planPrice) {
            return redirect()->route('filament.tenant.pages.billing')
                ->with('billing_error', __('That billing cycle is not available for this plan.'));
        }

        if (! $planPrice->stripe_price_id) {
            return redirect()->route('filament.tenant.pages.billing')
                ->with('billing_error', __('This plan/cycle is not yet configured for purchase. Please contact support.'));
        }

        $successUrl = url('/manage/billing?upgraded=1');
        $cancelUrl  = url('/manage/billing');

        $url = $this->billing->createCheckoutSession($tenant, $planPrice, $successUrl, $cancelUrl);

        if (! $url) {
            return redirect()->route('filament.tenant.pages.billing')
                ->with('billing_error', __('Unable to start the payment process. Please try again in a moment.'));
        }

        return redirect()->away($url);
    }
}
