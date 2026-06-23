<?php

namespace App\Filament\Resources\SubscriptionPlans\Pages;

use App\Filament\Resources\SubscriptionPlans\SubscriptionPlanResource;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class CreateSubscriptionPlan extends CreateRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    /**
     * After the plan + price rows are persisted, auto-create the Stripe
     * Product and one Price per paid billing cycle.
     */
    protected function afterCreate(): void
    {
        $plan = $this->record;

        $secret = config('services.stripe.secret') ?: Setting::get('stripe_secret');

        if (! $secret) {
            Notification::make()
                ->title(__('Plan saved — Stripe not configured'))
                ->body(__('Add your Stripe Secret Key in Settings → Payments to enable automatic billing.'))
                ->warning()
                ->send();
            return;
        }

        // Reload prices so we have a fresh collection
        $plan->load('prices');
        $paidPrices = $plan->prices->filter(fn ($p) => (float) $p->price > 0);

        if ($paidPrices->isEmpty()) {
            // Free plan — nothing to create in Stripe
            return;
        }

        try {
            Stripe::setApiKey($secret);

            // 1. Create one Stripe Product for the plan
            $product = Product::create([
                'name'     => $plan->name,
                'metadata' => ['managed' => 'slotara', 'plan_id' => $plan->id],
            ]);
            $plan->update(['stripe_product_id' => $product->id]);

            // 2. Create one Stripe Price per paid billing cycle
            $created = 0;
            foreach ($paidPrices as $priceEntry) {
                if ($priceEntry->stripe_price_id) {
                    continue; // already has one (shouldn't happen on create, but be safe)
                }

                $interval = match ($priceEntry->billing_cycle) {
                    'yearly'  => 'year',
                    'weekly'  => 'week',
                    default   => 'month',
                };

                $stripePrice = Price::create([
                    'product'     => $product->id,
                    'unit_amount' => (int) round((float) $priceEntry->price * 100),
                    'currency'    => 'usd',
                    'recurring'   => ['interval' => $interval],
                    'metadata'    => [
                        'managed'       => 'slotara',
                        'plan_id'       => $plan->id,
                        'billing_cycle' => $priceEntry->billing_cycle,
                    ],
                ]);

                $priceEntry->update(['stripe_price_id' => $stripePrice->id]);
                $created++;
            }

            Notification::make()
                ->title(__('Stripe Product & Prices created'))
                ->body("Product: {$product->id} · {$created} price(s) generated automatically.")
                ->success()
                ->send();

        } catch (ApiErrorException $e) {
            Notification::make()
                ->title(__('Stripe API error — prices not synced'))
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title(__('Unexpected error during Stripe sync'))
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
