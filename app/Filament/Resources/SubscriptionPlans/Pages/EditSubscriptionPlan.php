<?php

namespace App\Filament\Resources\SubscriptionPlans\Pages;

use App\Filament\Resources\SubscriptionPlans\SubscriptionPlanResource;
use App\Models\Setting;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class EditSubscriptionPlan extends EditRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    /** Original name captured before the save to detect renames. */
    protected ?string $originalName = null;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function beforeSave(): void
    {
        $this->originalName = $this->record->getOriginal('name');
    }

    /**
     * After saving:
     * 1. If the plan was renamed → update the Stripe Product name.
     * 2. For any new price rows that don't have a stripe_price_id yet → auto-create.
     */
    protected function afterSave(): void
    {
        $plan   = $this->record;
        $secret = config('services.stripe.secret') ?: Setting::get('stripe_secret');

        if (! $secret) {
            return; // Stripe not configured — silent skip
        }

        try {
            Stripe::setApiKey($secret);

            // ── 1. Sync product name if plan was renamed ──────────────────
            if (
                $plan->stripe_product_id
                && $this->originalName !== null
                && $this->originalName !== $plan->name
            ) {
                Product::update($plan->stripe_product_id, ['name' => $plan->name]);
            }

            // ── 2. Auto-create Stripe Prices for new rows ─────────────────
            $plan->load('prices');
            $missing = $plan->prices->filter(
                fn ($p) => (float) $p->price > 0 && ! $p->stripe_price_id
            );

            if ($missing->isEmpty()) {
                return;
            }

            // Ensure we have a Stripe Product to attach prices to
            $productId = $plan->stripe_product_id;
            if (! $productId) {
                $product   = Product::create([
                    'name'     => $plan->name,
                    'metadata' => ['managed' => 'slotara', 'plan_id' => $plan->id],
                ]);
                $productId = $product->id;
                $plan->update(['stripe_product_id' => $productId]);
            }

            $created = 0;
            foreach ($missing as $priceEntry) {
                $interval = match ($priceEntry->billing_cycle) {
                    'yearly'  => 'year',
                    'weekly'  => 'week',
                    default   => 'month',
                };

                $stripePrice = Price::create([
                    'product'     => $productId,
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

            if ($created > 0) {
                Notification::make()
                    ->title("{$created} new Stripe Price(s) created")
                    ->body(__('New billing cycle(s) have been synced to Stripe automatically.'))
                    ->success()
                    ->send();
            }

        } catch (ApiErrorException $e) {
            Notification::make()
                ->title(__('Stripe sync error'))
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
