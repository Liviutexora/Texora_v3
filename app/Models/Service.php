<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'duration_minutes',
        'price',
        'currency',
        'color',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'            => 'decimal:2',
            'duration_minutes' => 'integer',
            'sort_order'       => 'integer',
            'is_active'        => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'provider_services');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(SlotReservation::class);
    }

    public function getDurationLabelAttribute(): string
    {
        if ($this->duration_minutes < 60) {
            return "{$this->duration_minutes} min";
        }

        $hours   = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        return $minutes > 0 ? "{$hours}h {$minutes}min" : "{$hours}h";
    }

    public function getPriceFormattedAttribute(): string
    {
        if ($this->price == 0) {
            return 'Free';
        }

        return $this->currency . ' ' . number_format($this->price, 2);
    }
}
