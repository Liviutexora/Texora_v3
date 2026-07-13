<?php

namespace App\Models;

use App\Support\SubscriptionCurrency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'max_providers',
        'max_services',
        'max_bookings_per_month',
        'features',
        'is_active',
        'sort_order',
        // Legacy columns kept in DB for backward compat — managed via prices() going forward
        'price',
        'billing_cycle',
        'stripe_price_id',
        'stripe_product_id',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            'features'  => 'array',
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function prices(): HasMany
    {
        return $this->hasMany(SubscriptionPlanPrice::class, 'plan_id')
            ->orderBy('sort_order');
    }

    public function activePrices(): HasMany
    {
        return $this->hasMany(SubscriptionPlanPrice::class, 'plan_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Return the price entry for a given billing cycle, or null if not offered. */
    public function priceFor(string $cycle): ?SubscriptionPlanPrice
    {
        return $this->prices->firstWhere('billing_cycle', $cycle);
    }

    /** The lowest active price amount (for display / free plan detection). */
    public function lowestPrice(): float
    {
        $p = $this->activePrices()->min('price');
        return $p !== null ? (float) $p : (float) ($this->price ?? 0);
    }

    /** True if this plan is free (all prices are zero or no prices defined). */
    public function isFree(): bool
    {
        return $this->lowestPrice() === 0.0;
    }

    /** Display string: "29 / mo · 278 / yr" with configured currency prefix. */
    public function priceDisplay(): string
    {
        $currency = SubscriptionCurrency::symbol() . ' ';
        $prices = $this->activePrices()->get();

        if ($prices->isEmpty()) {
            return $this->price ? $currency . number_format((float) $this->price, 0) : 'Free';
        }

        return $prices->map(fn ($p) => $currency . number_format((float) $p->price, 0) . ' / ' . $p->intervalShort())
            ->implode(' · ');
    }
}
