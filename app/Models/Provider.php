<?php

namespace App\Models;

use App\Enums\SlotOverrideStatus;
use App\Models\Traits\BelongsToTenant;
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
        $dayOfWeek = $date->dayOfWeekIso;
        $dateOnly = $date->toDateString();

        $shifts = $this->relationLoaded('shifts')
            ? $this->shifts->filter(fn (ProviderShift $s) => in_array($dayOfWeek, $s->available_days ?? [], true))
            : $this->shifts()->whereJsonContains('available_days', $dayOfWeek)->get();

        // Get overrides for this date
        // When loaded via relation, date might be a Carbon instance, so we need to filter properly
        if ($this->relationLoaded('slotOverrides')) {
            $overrides = $this->slotOverrides->filter(function ($override) use ($dateOnly) {
                $overrideDate = $override->date instanceof \Carbon\Carbon 
                    ? $override->date->toDateString() 
                    : $override->date;
                return $overrideDate === $dateOnly;
            });
        } else {
            $overrides = $this->slotOverrides()->where('date', $dateOnly)->get();
        }

        $slots = collect();
        foreach ($shifts as $shift) {
            $startTimeStr = $shift->start_time instanceof Carbon
                ? $shift->start_time->format('H:i')
                : $shift->start_time;
            $start = Carbon::parse($date->format('Y-m-d').' '.$startTimeStr);

            for ($i = 0; $i < $shift->number_of_slots; $i++) {
                $slotStart = $start->copy()->addMinutes(
                    $i * ($shift->slot_duration_minutes + $shift->buffer_minutes)
                );
                $slotEnd = $slotStart->copy()->addMinutes($shift->slot_duration_minutes);
                $status = 'available';
                
                // Format slot times for comparison
                $slotStartTime = $slotStart->format('H:i:s');
                $slotEndTime = $slotEnd->format('H:i:s');
                $reservation_id = NULL;
                // Check for overrides that match this slot
                foreach ($overrides as $override) {
                    // Get override times - they're stored as TIME type, so they come as strings like '08:00:00'
                    $oStartTime = $override->start_time;
                    $oEndTime = $override->end_time;
                    
                    // Ensure they're strings and in HH:mm:ss format
                    if ($oStartTime instanceof Carbon) {
                        $oStartTime = $oStartTime->format('H:i:s');
                    } elseif (!is_string($oStartTime)) {
                        $oStartTime = Carbon::parse($oStartTime)->format('H:i:s');
                    }
                    // Ensure format is HH:mm:ss (pad if needed)
                    if (strlen($oStartTime) === 5) {
                        $oStartTime .= ':00';
                    }
                    
                    if ($oEndTime instanceof Carbon) {
                        $oEndTime = $oEndTime->format('H:i:s');
                    } elseif (!is_string($oEndTime)) {
                        $oEndTime = Carbon::parse($oEndTime)->format('H:i:s');
                    }
                    // Ensure format is HH:mm:ss (pad if needed)
                    if (strlen($oEndTime) === 5) {
                        $oEndTime .= ':00';
                    }
                    
                    // Check if slot overlaps with override
                    // Overlap occurs when: slotStart < overrideEnd AND slotEnd > overrideStart
                    // Using string comparison which works for time strings in HH:mm:ss format
                    if ($slotStartTime < $oEndTime && $slotEndTime > $oStartTime) {
                        // Extract status value - handle enum or string
                        if ($override->status instanceof SlotOverrideStatus) {
                            $status = $override->status->value;
                        } elseif (is_string($override->status)) {
                            $status = $override->status;
                        } else {
                            $status = 'blocked'; // Default fallback
                        }
                        $reservation_id = $override->reservation_id;
                        break; // First matching override wins
                    }
                }
                $slots->push([
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'status' => $status,
                    'shift_name' => $shift->name,
                    'reservation_id' => $reservation_id,
                ]);
            }
        }

        return $slots->sortBy('start')->values();
    }
}
