<?php

namespace App\Services\Pwa\DTO;

final class PwaVersionData
{
    public function __construct(
        public readonly string $version,
        public readonly ?string $build = null,
        public readonly ?string $releaseDate = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'version' => $this->version,
            'build' => $this->build,
            'release_date' => $this->releaseDate,
        ], static fn ($value) => $value !== null && $value !== '');
    }
}