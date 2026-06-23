<?php

namespace App\Booking\Themes;

class ThemeRegistry
{
    /**
     * All registered booking wizard themes.
     * To add a new theme:
     *   1. Add an entry here.
     *   2. Create resources/views/livewire/booking/themes/{key}/layout.blade.php
     * No other files need touching.
     */
    public static function all(): array
    {
        return [
            'classic' => [
                'name'        => 'Classic',
                'description' => __('Clean, minimal — white background with brand accent'),
                'preview'     => 'single-column',
                'accent'      => '#7c3aed',
            ],
            'lumina' => [
                'name'        => 'Lumina',
                'description' => __('Two-column with branded gradient sidebar'),
                'preview'     => 'two-column',
                'accent'      => '#6d28d9',
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        return static::all()[$key] ?? null;
    }

    public static function exists(string $key): bool
    {
        return isset(static::all()[$key]);
    }

    public static function default(): string
    {
        return 'classic';
    }

    /** Validate and return a safe key, falling back to default. */
    public static function resolve(string $key): string
    {
        return static::exists($key) ? $key : static::default();
    }
}
