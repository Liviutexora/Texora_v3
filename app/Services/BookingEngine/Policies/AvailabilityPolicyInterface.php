<?php

namespace App\Services\BookingEngine\Policies;

use Illuminate\Support\Collection;

interface AvailabilityPolicyInterface
{
    public function key(): string;

    /**
     * @return Collection<int, array{start: string, end: string, status: string, shift_name?: string, reservation_id?: mixed}>
     */
    public function resolve(AvailabilityPolicyContext $context): Collection;
}
