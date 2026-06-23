<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderShift extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'provider_id',
        'name',
        'slot_duration_minutes',
        'buffer_minutes',
        'start_time',
        'number_of_slots',
        'available_days',
    ];

    protected function casts(): array
    {
        return [
            'slot_duration_minutes' => 'integer',
            'buffer_minutes' => 'integer',
            'number_of_slots' => 'integer',
            'available_days' => 'array',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Compute end time from start_time + (number_of_slots * slot_duration_minutes) + ((number_of_slots - 1) * buffer_minutes).
     */
    public function getEndTimeAttribute(): string
    {
        $start = $this->start_time instanceof Carbon
            ? $this->start_time
            : Carbon::parse($this->start_time);
        $totalMinutes = $this->number_of_slots * $this->slot_duration_minutes
            + ($this->number_of_slots - 1) * $this->buffer_minutes;

        return $start->copy()->addMinutes($totalMinutes)->format('H:i');
    }

    /**
     * Get generated slot time ranges for a given date (time strings only).
     *
     * @return array<int, array{start: string, end: string}>
     */
    public function getSlotTimesForDate(Carbon $date): array
    {
        $startTimeStr = $this->start_time instanceof Carbon
            ? $this->start_time->format('H:i')
            : $this->start_time;
        $start = Carbon::parse($date->format('Y-m-d').' '.$startTimeStr);
        $slots = [];
        for ($i = 0; $i < $this->number_of_slots; $i++) {
            $slotStart = $start->copy()->addMinutes(
                $i * ($this->slot_duration_minutes + $this->buffer_minutes)
            );
            $slotEnd = $slotStart->copy()->addMinutes($this->slot_duration_minutes);
            $slots[] = [
                'start' => $slotStart->format('H:i'),
                'end' => $slotEnd->format('H:i'),
            ];
        }

        return $slots;
    }
}
