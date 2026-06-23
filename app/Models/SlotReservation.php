<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Provider;
use App\Models\User;
use App\Models\ProviderSlotOverride;

class SlotReservation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'service_id',
        'provider_id',
        'date',
        'start_time',
        'end_time',
        'user_id',
        'name',
        'gender',
        'age',
        'email',
        'phone',
        'note',
        'provider_note',
        'verification_method',
        'verification_token',
        'verification_expires_at',
        'is_verified',
        'status',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
        'amount',
        'currency',
        'payment_status',
        'payment_gateway',
        'payment_reference',
        'custom_answers',
        'cancellation_token',
        'reminder_sent_at',
        'sms_reminder_sent_at',
        'paid_at',
        'checkout_session_id',
        'google_calendar_event_id',
    ];

    protected function casts(): array
    {
        return [
            'date'                   => 'date',
            'verification_expires_at'=> 'datetime',
            'cancelled_at'           => 'datetime',
            'is_verified'            => 'boolean',
            'custom_answers'         => 'array',
            'reminder_sent_at'       => 'datetime',
            'sms_reminder_sent_at'   => 'datetime',
            'paid_at'                => 'datetime',
        ];
    }

    public function scopePending($query)  { return $query->where('status', 'pending'); }
    public function scopeConfirmed($query){ return $query->where('status', 'confirmed'); }
    public function scopeCancelled($query){ return $query->where('status', 'cancelled'); }
    public function scopeCompleted($query){ return $query->where('status', 'completed'); }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function override(): HasOne
    {
        return $this->hasOne(ProviderSlotOverride::class, 'reservation_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function providerRelation(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
}

