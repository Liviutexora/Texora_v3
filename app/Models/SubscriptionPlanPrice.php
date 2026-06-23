<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPlanPrice extends Model
{
    protected $fillable = [
        'plan_id',
        'billing_cycle',
        'price',
        'stripe_price_id',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /** Human-readable interval: "month", "year", "week" */
    public function intervalLabel(): string
    {
        return match($this->billing_cycle) {
            'yearly'  => 'year',
            'weekly'  => 'week',
            default   => 'month',
        };
    }

    /** Short display: "mo", "yr", "wk" */
    public function intervalShort(): string
    {
        return match($this->billing_cycle) {
            'yearly'  => 'yr',
            'weekly'  => 'wk',
            default   => 'mo',
        };
    }

    /** e.g. "Monthly", "Annually", "Weekly" */
    public function cycleLabel(): string
    {
        return match($this->billing_cycle) {
            'yearly'  => 'Annually',
            'weekly'  => 'Weekly',
            default   => 'Monthly',
        };
    }
}
