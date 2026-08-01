<?php

namespace App\Services\Pwa\Contracts;

use App\Services\Pwa\DTO\PwaVersionData;

interface PwaVersionSourceInterface
{
    public function current(): PwaVersionData;
}