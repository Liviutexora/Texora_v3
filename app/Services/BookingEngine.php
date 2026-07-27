<?php

namespace App\Services;

use App\Enums\SlotOverrideStatus;
use App\Models\Provider;
use App\Models\ProviderShift;
use App\Models\SlotReservation;
use App\Services\BookingEngine\Policies\AvailabilityPolicyContext;
use App\Services\BookingEngine\Policies\AvailabilityPolicyInterface;
use App\Services\BookingEngine\Policies\BalancedSchedulingPolicy;
use App\Services\BookingEngine\Policies\CompactSchedulingPolicy;
use App\Services\BookingEngine\Policies\FillDeadHoursPolicy;
use App\Services\BookingEngine\Policies\FirstAvailablePolicy;
use App\Services\BookingEngine\Policies\PreferredProviderPolicy;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingEngine
{
    public const DEFAULT_AVAILABILITY_POLICY = 'first_available';

    /**
     * Single high-level entrypoint for provider availability windows.
     *
     * @param Collection<int, mixed>|int $selectedServicesOrRequiredDuration
     * @param Collection<int, array{start: string, end: string, status: string, shift_name?: string, reservation_id?: mixed}>|null $baseSlots
     * @return Collection<int, array{start: string, end: string, status: string, shift_name?: string, reservation_id?: mixed}>
     */
    public function getReservableStartsForProvider(
        Provider $provider,
        Carbon $date,
        Collection|int $selectedServicesOrRequiredDuration,
        int $bufferMinutes = 0,
        ?int $excludeReservationId = null,
        ?Collection $baseSlots = null,
        ?string $policyKey = null,
    ): Collection {
        $requiredDurationMinutes = $this->resolveRequiredDurationMinutes($selectedServicesOrRequiredDuration);
        if ($requiredDurationMinutes <= 0) {
            return collect();
        }

        $sourceSlots = ($baseSlots ?? $this->getSlotsForDate($provider, $date))->values();
        $busyIntervals = $this->resolveBusyIntervalsForProviderDate($provider, $date, $excludeReservationId);

        return $this->executeAvailabilityPolicy(
            $sourceSlots,
            $busyIntervals,
            $requiredDurationMinutes,
            max(0, $bufferMinutes),
            $policyKey,
        );
    }

    /**
     * Generate slots for a provider and date using the current legacy rules.
     *
     * This method is the compatibility entrypoint for the existing slot engine behavior.
     * It intentionally preserves today's algorithm and output structure.
     *
     * @return Collection<int, array{start: string, end: string, status: string, shift_name: string, reservation_id: mixed}>
     */
    public function getSlotsForDate(Provider $provider, Carbon $date): Collection
    {
        $dayOfWeek = $date->dayOfWeekIso;
        $dateOnly = $date->toDateString();

        $shifts = $this->resolveShiftsForDate($provider, $dayOfWeek);
        $overrides = $this->resolveOverridesForDate($provider, $dateOnly);

        $slots = collect();
        foreach ($shifts as $shift) {
            $startTimeStr = $shift->start_time instanceof Carbon
                ? $shift->start_time->format('H:i')
                : $shift->start_time;
            $start = Carbon::parse($date->format('Y-m-d') . ' ' . $startTimeStr);

            for ($i = 0; $i < $shift->number_of_slots; $i++) {
                $slotStart = $start->copy()->addMinutes(
                    $i * ($shift->slot_duration_minutes + $shift->buffer_minutes)
                );
                $slotEnd = $slotStart->copy()->addMinutes($shift->slot_duration_minutes);
                $status = 'available';

                // Format slot times for comparison
                $slotStartTime = $slotStart->format('H:i:s');
                $slotEndTime = $slotEnd->format('H:i:s');
                $reservation_id = null;

                // Check for overrides that match this slot
                foreach ($overrides as $override) {
                    $oStartTime = $this->normalizeTimeValue($override->start_time);
                    $oEndTime = $this->normalizeTimeValue($override->end_time);

                    // Check if slot overlaps with override
                    // Overlap occurs when: slotStart < overrideEnd AND slotEnd > overrideStart
                    // Using string comparison which works for time strings in HH:mm:ss format
                    if ($this->doesSlotOverlapOverride($slotStartTime, $slotEndTime, $oStartTime, $oEndTime)) {
                        $status = $this->resolveSlotStatusFromOverride($override->status);
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

    /**
     * Resolve provider shifts for a given ISO weekday.
     *
     * @return Collection<int, ProviderShift>
     */
    protected function resolveShiftsForDate(Provider $provider, int $dayOfWeek): Collection
    {
        return $provider->relationLoaded('shifts')
            ? $provider->shifts->filter(fn (ProviderShift $shift) => in_array($dayOfWeek, $shift->available_days ?? [], true))
            : $provider->shifts()->whereJsonContains('available_days', $dayOfWeek)->get();
    }

    /**
     * Resolve provider slot overrides for a specific date.
     *
     * @return Collection<int, mixed>
     */
    protected function resolveOverridesForDate(Provider $provider, string $dateOnly): Collection
    {
        // When loaded via relation, date might be a Carbon instance, so we need to filter properly
        if ($provider->relationLoaded('slotOverrides')) {
            return $provider->slotOverrides->filter(function ($override) use ($dateOnly) {
                $overrideDate = $override->date instanceof Carbon
                    ? $override->date->toDateString()
                    : $override->date;

                return $overrideDate === $dateOnly;
            });
        }

        return $provider->slotOverrides()->where('date', $dateOnly)->get();
    }

    /**
     * Normalize a time value into HH:mm:ss string format.
     */
    protected function normalizeTimeValue(mixed $value): string
    {
        // Get override times - they're stored as TIME type, so they come as strings like '08:00:00'
        if ($value instanceof Carbon) {
            $time = $value->format('H:i:s');
        } elseif (! is_string($value)) {
            $time = Carbon::parse($value)->format('H:i:s');
        } else {
            $time = $value;
        }

        // Ensure format is HH:mm:ss (pad if needed)
        if (strlen($time) === 5) {
            $time .= ':00';
        }

        return $time;
    }

    /**
     * Determine whether a generated slot overlaps an override interval.
     */
    protected function doesSlotOverlapOverride(string $slotStartTime, string $slotEndTime, string $overrideStartTime, string $overrideEndTime): bool
    {
        return $slotStartTime < $overrideEndTime && $slotEndTime > $overrideStartTime;
    }

    /**
     * Resolve a stable slot status string from an override status value.
     */
    protected function resolveSlotStatusFromOverride(mixed $status): string
    {
        // Extract status value - handle enum or string
        if ($status instanceof SlotOverrideStatus) {
            return $status->value;
        }

        if (is_string($status)) {
            return $status;
        }

        return 'blocked'; // Default fallback
    }

    /**
     * Build reservation windows for a requested duration.
     *
     * Reserved for future phases where service duration + buffer based windows
     * are calculated centrally by BookingEngine.
     *
     * @param Collection<int, array{start: string, end: string, status: string, shift_name: string, reservation_id: mixed}> $slots
     * @return Collection<int, array<string, mixed>>
     */
    public function buildReservableWindows(Collection $slots, int $requiredDurationMinutes, int $bufferMinutes = 0): Collection
    {
        $firstAvailablePolicy = $this->resolveFirstAvailablePolicy();

        return $firstAvailablePolicy->buildReservableWindows($slots, $requiredDurationMinutes, $bufferMinutes);
    }

    /**
     * Mark generated slots as reserved when they overlap existing reservation intervals.
     *
     * @param Collection<int, array{start: string, end: string, status: string, shift_name?: string, reservation_id?: mixed}> $slots
     * @param Collection<int, array{start: mixed, end: mixed, reservation_id?: mixed}> $busyIntervals
     * @return Collection<int, array{start: string, end: string, status: string, shift_name?: string, reservation_id?: mixed}>
     */
    public function applyBusyIntervalsToSlots(Collection $slots, Collection $busyIntervals): Collection
    {
        $firstAvailablePolicy = $this->resolveFirstAvailablePolicy();

        return $firstAvailablePolicy->applyBusyIntervalsToSlots($slots, $busyIntervals);
    }

    /**
     * Calculate total duration in minutes from selected services only.
     *
     * @param Collection<int, mixed> $services
     */
    public function calculateTotalDurationMinutes(Collection $services): int
    {
        return (int) $services
            ->sum(fn ($service) => max(0, (int) ($service->duration_minutes ?? 0)));
    }

    /**
     * Resolve required minutes from selected services collection or direct duration.
     *
     * @param Collection<int, mixed>|int $selectedServicesOrRequiredDuration
     */
    protected function resolveRequiredDurationMinutes(Collection|int $selectedServicesOrRequiredDuration): int
    {
        if (is_int($selectedServicesOrRequiredDuration)) {
            return max(0, $selectedServicesOrRequiredDuration);
        }

        return $this->calculateTotalDurationMinutes($selectedServicesOrRequiredDuration);
    }

    /**
     * Resolve existing provider reservations as busy intervals for one date.
     *
     * @return Collection<int, array{start: mixed, end: mixed, reservation_id: int}>
     */
    protected function resolveBusyIntervalsForProviderDate(Provider $provider, Carbon $date, ?int $excludeReservationId = null): Collection
    {
        return SlotReservation::withoutGlobalScope('tenant')
            ->where('provider_id', $provider->id)
            ->whereDate('date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($excludeReservationId, fn ($q) => $q->where('id', '!=', $excludeReservationId))
            ->get(['id', 'start_time', 'end_time'])
            ->map(fn ($reservation) => [
                'start' => $reservation->start_time,
                'end' => $reservation->end_time ?: $reservation->start_time,
                'reservation_id' => $reservation->id,
            ]);
    }

    /**
     * Add minutes to a normalized HH:mm:ss value and return HH:mm:ss.
     */
    protected function addMinutesToTimeValue(string $time, int $minutes): string
    {
        return Carbon::createFromFormat('H:i:s', $time)
            ->addMinutes($minutes)
            ->format('H:i:s');
    }

    /**
     * Validate whether a proposed reservation interval is allowed.
     *
     * Centralized final validation entrypoint used by all booking flows.
     */
    public function validateReservationInterval(
        Provider|int|null $provider,
        Carbon $date,
        string $startTime,
        string $endTime,
        ?int $excludeReservationId = null,
        int $bufferMinutes = 0,
        ?Collection $baseSlots = null,
    ): bool {
        $providerModel = $this->resolveProviderModel($provider);
        if (! $providerModel) {
            return false;
        }

        $normalizedStart = $this->normalizeTimeValue($startTime);
        $normalizedEnd = $this->normalizeTimeValue($endTime);

        if ($normalizedEnd <= $normalizedStart) {
            return false;
        }

        $requiredDurationMinutes = Carbon::createFromFormat('H:i:s', $normalizedStart)
            ->diffInMinutes(Carbon::createFromFormat('H:i:s', $normalizedEnd));

        if ($requiredDurationMinutes <= 0) {
            return false;
        }

        $busyIntervals = $this->resolveBusyIntervalsForProviderDate($providerModel, $date, $excludeReservationId);
        $overlapsReservation = $busyIntervals->contains(fn (array $interval) => $this->doesSlotOverlapOverride(
            $normalizedStart,
            $normalizedEnd,
            $this->normalizeTimeValue($interval['start'] ?? '00:00:00'),
            $this->normalizeTimeValue($interval['end'] ?? '00:00:00'),
        ));

        if ($overlapsReservation) {
            return false;
        }

        // When explicit base slots are provided (tenant availability flow), ensure
        // the exact requested interval is a valid reservable window for that source.
        if ($baseSlots !== null) {
            $windows = $this->executeAvailabilityPolicy(
                $baseSlots->values(),
                $busyIntervals,
                $requiredDurationMinutes,
                $bufferMinutes,
            );

            $expectedStart = substr($normalizedStart, 0, 5);
            $expectedEnd = substr($normalizedEnd, 0, 5);

            return $windows->contains(fn (array $window) =>
                ($window['start'] ?? null) === $expectedStart
                && ($window['end'] ?? null) === $expectedEnd
            );
        }

        return true;
    }

    protected function resolveProviderModel(Provider|int|null $provider): ?Provider
    {
        if ($provider instanceof Provider) {
            if ($provider->exists) {
                return $provider;
            }

            return $provider->getKey() ? Provider::withoutGlobalScope('tenant')->find($provider->getKey()) : null;
        }

        if (is_int($provider) && $provider > 0) {
            return Provider::withoutGlobalScope('tenant')->find($provider);
        }

        return null;
    }

    protected function executeAvailabilityPolicy(
        Collection $sourceSlots,
        Collection $busyIntervals,
        int $requiredDurationMinutes,
        int $bufferMinutes = 0,
        ?string $policyKey = null,
    ): Collection {
        $policy = $this->resolveAvailabilityPolicy($policyKey);
        $context = new AvailabilityPolicyContext(
            sourceSlots: $sourceSlots,
            busyIntervals: $busyIntervals,
            requiredDurationMinutes: $requiredDurationMinutes,
            bufferMinutes: max(0, $bufferMinutes),
        );

        return $policy->resolve($context);
    }

    protected function resolveAvailabilityPolicy(?string $policyKey = null): AvailabilityPolicyInterface
    {
        $key = strtolower(trim((string) ($policyKey ?: self::DEFAULT_AVAILABILITY_POLICY)));

        return match ($key) {
            'compact_scheduling' => app(CompactSchedulingPolicy::class),
            'balanced_scheduling' => app(BalancedSchedulingPolicy::class),
            'fill_dead_hours' => app(FillDeadHoursPolicy::class),
            'preferred_provider' => app(PreferredProviderPolicy::class),
            default => app(FirstAvailablePolicy::class),
        };
    }

    protected function resolveFirstAvailablePolicy(): FirstAvailablePolicy
    {
        $policy = $this->resolveAvailabilityPolicy(self::DEFAULT_AVAILABILITY_POLICY);

        if ($policy instanceof FirstAvailablePolicy) {
            return $policy;
        }

        return app(FirstAvailablePolicy::class);
    }
}
