<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerFollowup extends Model
{
    protected $fillable = [
        'tenant_id',
        'slot_reservation_id',
        'user_id',
        'service_id',
        'provider_id',
        'type',
        'channel',
        'status',
        'priority',
        'scheduled_at',
        'next_followup_at',
        'last_action',
        'last_action_at',
        'completed_at',
        'token',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'next_followup_at' => 'datetime',
            'last_action_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function slotReservation(): BelongsTo
    {
        return $this->belongsTo(SlotReservation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(CustomerFollowupHistory::class, 'followup_id');
    }
}
