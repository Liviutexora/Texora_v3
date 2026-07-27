<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Services\BookingEngine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Provider extends Model
{
    /** @use HasFactory<\Database\Factories\ProviderFactory> */
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'job_title',
        'license_number',
        'bio',
        'color',
        'experience_years',
        'is_active',
        'calendar_sync_enabled',
        'google_calendar_id',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'               => 'boolean',
            'calendar_sync_enabled'   => 'boolean',
            'google_token_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(ProviderShift::class)->orderBy('id');
    }

    public function slotOverrides(): HasMany
    {
        return $this->hasMany(ProviderSlotOverride::class)->orderBy('date')->orderBy('start_time');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'provider_services');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(SlotReservation::class, 'provider_id');
    }

    public static function weekDays(): array
    {
        return [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];
    }

    /**
     * Get generated slots for a date from shifts, with blocked/reserved marked from overrides.
     *
     * @return \Illuminate\Support\Collection<int, array{start: string, end: string, status: string, shift_name: string}>
     */
    public function getSlotsForDate(Carbon $date): \Illuminate\Support\Collection
    {
        return app(BookingEngine::class)->getSlotsForDate($this, $date);
    }
}
