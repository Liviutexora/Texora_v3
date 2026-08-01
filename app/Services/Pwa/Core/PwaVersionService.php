<?php

namespace App\Services\Pwa\Core;

use App\Services\Pwa\Contracts\PwaVersionSourceInterface;
use App\Services\Pwa\DTO\PwaVersionData;

class PwaVersionService
{
    public function __construct(private readonly PwaVersionSourceInterface $versionSource)
    {
    }

    public function current(): PwaVersionData
    {
        return $this->versionSource->current();
    }
}