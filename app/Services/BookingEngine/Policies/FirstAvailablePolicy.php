<?php

namespace App\Services\BookingEngine\Policies;

use App\Enums\SlotOverrideStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FirstAvailablePolicy implements AvailabilityPolicyInterface
{
    public function key(): string
    {
        return 'first_available';
    }

    public function resolve(AvailabilityPolicyContext $context): Collection
    {
        return $this->buildReservableWindows(
            $this->applyBusyIntervalsToSlots($context->sourceSlots, $context->busyIntervals),
            $context->requiredDurationMinutes,
            $context->bufferMinutes,
        );
    }

    /**
     * @param Collection<int, array{start: string, end: string, status: string, shift_name?: string, reservation_id?: mixed}> $slots
     * @return Collection<int, array{start: string, end: string, status: string, shift_name?: string, reservation_id?: mixed}>
     */
    public function buildReservableWindows(Collection $slots, int $requiredDurationMinutes, int $bufferMinutes = 0): Collection
    {
        if ($requiredDurationMinutes <= 0) {
            return collect();
        }

        $requiredIntervalMinutes = $requiredDurationMinutes + max(0, $bufferMinutes);

        $normalizedSlots = $slots
            ->map(function (array $slot): array {
                return [
                    ...$slot,
                    '_start_time' => $this->normalizeTimeValue($slot['start'] ?? '00:00:00'),
                    '_end_time' => $this->normalizeTimeValue($slot['end'] ?? '00:00:00'),
                ];
            })
            ->sortBy('_start_time')
            ->values();

        $windows = collect();

        for ($i = 0; $i < $normalizedSlots->count(); $i++) {
            $slot = $normalizedSlots[$i];
            if (($slot['status'] ?? null) !== 'available') {
                continue;
            }

            $windowStart = $slot['_start_time'];
            $windowEnd = $this->addMinutesToTimeValue($windowStart, $requiredIntervalMinutes);

            $coverageEnd = $slot['_end_time'];
            for ($j = $i + 1; $j < $normalizedSlots->count(); $j++) {
                $next = $normalizedSlots[$j];
                if (($next['status'] ?? null) !== 'available') {
                    break;
                }
                if ($next['_start_time'] !== $coverageEnd) {
                    break;
                }

                $coverageEnd = $next['_end_time'];
            }

            if ($windowEnd <= $coverageEnd) {
                $windows->push([
                    'start' => substr($windowStart, 0, 5),
                    'end' => substr($windowEnd, 0, 5),
                    'status' => 'available',
                    'shift_name' => $slot['shift_name'] ?? null,
                    'reservation_id' => null,
                ]);
            }
        }

        return $windows
            ->unique(fn (array $window) => ($window['start'] ?? '') . '|' . ($window['end'] ?? ''))
            ->values();
    }

    /**
     * @param Collection<int, array{start: string, end: string, status: string, shift_name?: string, reservation_id?: mixed}> $slots
     * @param Collection<int, array{start: mixed, end: mixed, reservation_id?: mixed}> $busyIntervals
     * @return Collection<int, array{start: string, end: string, status: string, shift_name?: string, reservation_id?: mixed}>
     */
    public function applyBusyIntervalsToSlots(Collection $slots, Collection $busyIntervals): Collection
    {
        if ($slots->isEmpty() || $busyIntervals->isEmpty()) {
            return $slots;
        }

        $normalizedBusyIntervals = $busyIntervals->map(function (array $interval): array {
            $start = $this->normalizeTimeValue($interval['start'] ?? '00:00:00');
            $end = $this->normalizeTimeValue($interval['end'] ?? '00:00:00');

            return [
                'start' => $start,
                'end' => $end,
                'reservation_id' => $interval['reservation_id'] ?? null,
            ];
        })->values();

        return $slots->map(function (array $slot) use ($normalizedBusyIntervals): array {
            if (($slot['status'] ?? null) !== 'available') {
                return $slot;
            }

            $slotStart = $this->normalizeTimeValue($slot['start'] ?? '00:00:00');
            $slotEnd = $this->normalizeTimeValue($slot['end'] ?? '00:00:00');

            foreach ($normalizedBusyIntervals as $interval) {
                if ($this->doesSlotOverlapInterval($slotStart, $slotEnd, $interval['start'], $interval['end'])) {
                    $slot['status'] = SlotOverrideStatus::Reserved->value;
                    $slot['reservation_id'] = $interval['reservation_id'];
                    break;
                }
            }

            return $slot;
        })->values();
    }

    protected function normalizeTimeValue(mixed $value): string
    {
        if ($value instanceof Carbon) {
            $time = $value->format('H:i:s');
        } elseif (! is_string($value)) {
            $time = Carbon::parse($value)->format('H:i:s');
        } else {
            $time = $value;
        }

        if (strlen($time) === 5) {
            $time .= ':00';
        }

        return $time;
    }

    protected function doesSlotOverlapInterval(string $slotStart, string $slotEnd, string $intervalStart, string $intervalEnd): bool
    {
        return $slotStart < $intervalEnd && $slotEnd > $intervalStart;
    }

    protected function addMinutesToTimeValue(string $time, int $minutes): string
    {
        return Carbon::createFromFormat('H:i:s', $time)
            ->addMinutes($minutes)
            ->format('H:i:s');
    }
}
