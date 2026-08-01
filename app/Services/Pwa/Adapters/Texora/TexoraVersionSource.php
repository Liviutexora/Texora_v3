<?php

namespace App\Services\Pwa\Adapters\Texora;

use App\Services\Pwa\Contracts\PwaVersionSourceInterface;
use App\Services\Pwa\DTO\PwaVersionData;

class TexoraVersionSource implements PwaVersionSourceInterface
{
    public function current(): PwaVersionData
    {
        $path = base_path('version.json');
        $payload = [];

        if (is_file($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        $version = (string) ($payload['version'] ?? config('app.version', '1.0.0'));

        return new PwaVersionData(
            version: $version,
            build: $payload['build'] ?? $version,
            releaseDate: $payload['release_date'] ?? null,
        );
    }
}