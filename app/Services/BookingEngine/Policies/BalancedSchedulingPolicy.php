<?php

namespace App\Services\BookingEngine\Policies;

use Illuminate\Support\Collection;

class BalancedSchedulingPolicy implements AvailabilityPolicyInterface
{
    public function __construct(private readonly FirstAvailablePolicy $fallbackPolicy)
    {
    }

    public function key(): string
    {
        return 'balanced_scheduling';
    }

    public function resolve(AvailabilityPolicyContext $context): Collection
    {
        // Stage 5 skeleton: delegates to current default behavior.
        return $this->fallbackPolicy->resolve($context);
    }
}
