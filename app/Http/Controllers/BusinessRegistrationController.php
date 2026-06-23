<?php

namespace App\Http\Controllers;

use App\Helpers\PasswordPolicyRulesHelper;
use App\Models\PasswordPolicy;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanPrice;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class BusinessRegistrationController extends Controller
{
    public function create(): View
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->with('activePrices')
            ->orderBy('sort_order')
            ->get();

        $policy = PasswordPolicy::getDefault() ?? PasswordPolicy::first();
        $passwordMinLength = $policy?->min_length ?? 8;

        return view('auth.register', compact('plans', 'passwordMinLength'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'      => array_merge(['confirmed'], PasswordPolicyRulesHelper::rules()),
            'plan_id'       => ['required', 'exists:subscription_plans,id'],
            'billing_cycle' => ['nullable', 'string', 'in:monthly,yearly,weekly'],
        ], PasswordPolicyRulesHelper::messages());

        // Verify reCAPTCHA if enabled
        $recaptchaErrors = app(RecaptchaService::class)->validate($request);
        if (! empty($recaptchaErrors)) {
            return back()->withErrors($recaptchaErrors)->withInput();
        }

        $plan = SubscriptionPlan::with('activePrices')->findOrFail($request->plan_id);

        // Determine billing cycle — default to the first available cycle for this plan
        $billingCycle = $request->billing_cycle ?: 'monthly';
        $planPrice    = $plan->activePrices->firstWhere('billing_cycle', $billingCycle)
                        ?? $plan->activePrices->first();

        $isFree = ! $planPrice || (float) $planPrice->price === 0.0;

        // Persist registration data so we can recover it after Stripe redirect
        session([
            'pending_registration' => [
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => $request->password,
                'plan_id'       => $plan->id,
                'billing_cycle' => $billingCycle,
            ],
        ]);

        // Free plan — skip Stripe entirely
        if ($isFree) {
            return $this->createUser($request->name, $request->email, $request->password, $plan->id);
        }

        // Resolve the Stripe Price ID: prefer the prices table, fall back to legacy column
        $stripePriceId = $planPrice?->stripe_price_id ?? $plan->stripe_price_id ?? null;

        if (! $stripePriceId) {
            Log::error('Stripe Price ID missing for plan', [
                'plan_id'       => $plan->id,
                'billing_cycle' => $billingCycle,
            ]);

            return back()->withInput()
                ->withErrors(['stripe' => __('This plan is not yet available for purchase. Please contact support.')]);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $checkoutSession = StripeSession::create([
                'mode'                 => 'subscription',
                'payment_method_types' => ['card'],
                'line_items'           => [[
                    'price'    => $stripePriceId,
                    'quantity' => 1,
                ]],
                'subscription_data'    => [
                    'metadata' => [
                        'plan_id'       => $plan->id,
                        'plan_name'     => $plan->name,
                        'billing_cycle' => $billingCycle,
                    ],
                ],
                'customer_email' => $request->email,
                'metadata'       => [
                    'registrant_name'  => $request->name,
                    'registrant_email' => $request->email,
                    'plan_id'          => $plan->id,
                    'billing_cycle'    => $billingCycle,
                ],
                'success_url' => route('register.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('register.cancel'),
            ]);

            return redirect($checkoutSession->url);
        } catch (\Exception $e) {
            Log::error('Stripe Checkout (subscription) creation failed', ['error' => $e->getMessage()]);

            return back()->withInput()
                ->withErrors(['stripe' => __('Payment service unavailable. Please try again later.')]);
        }
    }

    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');
        $pending   = session('pending_registration');

        if (! $pending) {
            return redirect()->route('register')
                ->withErrors(['general' => __('Registration session expired. Please try again.')]);
        }

        $stripeCustomerId     = null;
        $stripeSubscriptionId = null;
        $stripeSubStatus      = null;

        if ($sessionId) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $stripeSession = StripeSession::retrieve([
                    'id'     => $sessionId,
                    'expand' => ['subscription'],
                ]);

                if ($stripeSession->payment_status === 'unpaid' && $stripeSession->mode !== 'subscription') {
                    return redirect()->route('register')
                        ->withErrors(['general' => __('Payment was not completed. Please try again.')]);
                }

                $stripeCustomerId     = $stripeSession->customer;
                $stripeSubscriptionId = is_string($stripeSession->subscription)
                    ? $stripeSession->subscription
                    : $stripeSession->subscription?->id;
                $stripeSubStatus      = is_object($stripeSession->subscription)
                    ? $stripeSession->subscription->status
                    : null;
            } catch (\Exception $e) {
                Log::error('Stripe session retrieval failed', [
                    'error'      => $e->getMessage(),
                    'session_id' => $sessionId,
                ]);

                return redirect()->route('register')
                    ->withErrors(['general' => __('Could not verify payment. Please contact support.')]);
            }
        }

        return $this->createUser(
            $pending['name'],
            $pending['email'],
            $pending['password'],
            $pending['plan_id'],
            $stripeCustomerId,
            $stripeSubscriptionId,
            $stripeSubStatus,
        );
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('register')
            ->with('info', __('Subscription setup cancelled. You can try again whenever you are ready.'));
    }

    private function createUser(
        string  $name,
        string  $email,
        string  $password,
        int     $planId,
        ?string $stripeCustomerId     = null,
        ?string $stripeSubscriptionId = null,
        ?string $stripeSubStatus      = null,
    ): RedirectResponse {
        // Guard: if user already exists (e.g. page refresh after success), just log them in
        $existing = User::where('email', $email)->first();
        if ($existing) {
            session()->forget('pending_registration');
            Auth::login($existing);

            return redirect('/admin');
        }

        $user = User::create([
            'name'      => $name,
            'email'     => $email,
            'password'  => Hash::make($password),
            'is_active' => true,
        ]);

        $user->assignRoleSingle('tenant_owner');

        session()->forget('pending_registration');

        // Pass Stripe data + plan to OnboardingWizard via session
        session([
            'signup_plan_id'              => $planId,
            'signup_stripe_customer_id'   => $stripeCustomerId,
            'signup_stripe_subscription_id' => $stripeSubscriptionId,
            'signup_stripe_sub_status'    => $stripeSubStatus,
        ]);

        Auth::login($user);

        return redirect()->route('tenant.setup');
    }
}
