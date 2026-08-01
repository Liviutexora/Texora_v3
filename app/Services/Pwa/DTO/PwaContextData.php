<?php

namespace App\Services\Pwa\DTO;

final class PwaContextData
{
    public function __construct(
        public readonly string $appId,
        public readonly string $startUrl,
        public readonly string $scope,
    ) {
    }

    public function with(?string $appId = null, ?string $startUrl = null, ?string $scope = null): self
    {
        return new self(
            $appId ?: $this->appId,
            $startUrl ?: $this->startUrl,
            $scope ?: $this->scope,
        );
    }

    public function manifestQuery(): array
    {
        return [
            'app_id' => $this->appId,
            'start_url' => $this->startUrl,
            'scope' => $this->scope,
        ];
    }
}