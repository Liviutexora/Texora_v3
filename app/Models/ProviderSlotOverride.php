<?php

namespace App\Models;

use App\Enums\SlotOverrideStatus;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SlotReservation;

class ProviderSlotOverride extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'provider_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'reservation_id',
        'reason',
        'external_event_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => SlotOverrideStatus::class,
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(SlotReservation::class, 'reservation_id');
    }
}

