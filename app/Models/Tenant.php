<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'plan_id',
        'status',
        'phone',
        'email',
        'address',
        'city',
        'country',
        'logo',
        'timezone',
        'currency',
        'website_url',
        'booking_page_color',
        'booking_page_tagline',
        'custom_fields',
        'trial_ends_at',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_subscription_status',
        'locale',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'custom_fields' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('sort_order');
    }

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(SlotReservation::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        return false;
    }

    public function isAccessible(): bool
    {
        // Manually suspended — always block
        if ($this->status === 'suspended') {
            return false;
        }

        // If a Stripe subscription exists, let its status decide access
        if ($this->stripe_subscription_status) {
            return in_array($this->stripe_subscription_status, ['trialing', 'active', 'past_due']);
        }

        // No Stripe subscription — allow if status is active
        return $this->status === 'active';
    }

    public function hasActiveSubscription(): bool
    {
        return in_array($this->stripe_subscription_status, ['trialing', 'active']);
    }

    public function getBookingUrl(): string
    {
        return url("/{$this->slug}");
    }
}
