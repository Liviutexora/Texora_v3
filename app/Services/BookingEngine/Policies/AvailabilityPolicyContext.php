<?php

namespace App\Services\BookingEngine\Policies;

use Illuminate\Support\Collection;

class AvailabilityPolicyContext
{
    /**
     * @param Collection<int, array{start: string, end: string, status: string, shift_name?: string, reservation_id?: mixed}> $sourceSlots
     * @param Collection<int, array{start: mixed, end: mixed, reservation_id?: mixed}> $busyIntervals
     */
    public function __construct(
        public readonly Collection $sourceSlots,
        public readonly Collection $busyIntervals,
        public readonly int $requiredDurationMinutes,
        public readonly int $bufferMinutes = 0,
    ) {
    }
}
