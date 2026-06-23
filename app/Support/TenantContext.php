<?php

namespace App\Support;

use App\Models\Tenant;

class TenantContext
{
    protected static ?Tenant $current = null;

    public static function set(Tenant $tenant): void
    {
        static::$current = $tenant;
    }

    public static function clear(): void
    {
        static::$current = null;
    }

    public static function current(): ?Tenant
    {
        return static::$current;
    }

    public static function id(): ?int
    {
        return static::$current?->id;
    }

    public static function isSet(): bool
    {
        return static::$current !== null;
    }
}
