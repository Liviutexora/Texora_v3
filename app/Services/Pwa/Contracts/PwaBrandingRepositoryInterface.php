<?php

namespace App\Services\Pwa\Contracts;

use App\Services\Pwa\DTO\PwaBrandingData;
use App\Services\Pwa\DTO\PwaContextData;

interface PwaBrandingRepositoryInterface
{
    public function getBranding(PwaContextData $context): PwaBrandingData;
}