<?php

namespace Tests\Unit;

use App\Booking\Themes\ThemeRegistry;
use Tests\TestCase;

class ThemeRegistryTest extends TestCase
{
    public function test_all_returns_expected_themes(): void
    {
        $themes = ThemeRegistry::all();
        $this->assertArrayHasKey('classic', $themes);
        $this->assertArrayHasKey('lumina', $themes);
    }

    public function test_get_returns_theme_data_for_valid_key(): void
    {
        $theme = ThemeRegistry::get('classic');
        $this->assertIsArray($theme);
        $this->assertArrayHasKey('name', $theme);
    }

    public function test_get_returns_null_for_invalid_key(): void
    {
        $this->assertNull(ThemeRegistry::get('does_not_exist'));
    }

    public function test_exists_returns_true_for_registered_theme(): void
    {
        $this->assertTrue(ThemeRegistry::exists('classic'));
        $this->assertTrue(ThemeRegistry::exists('lumina'));
    }

    public function test_exists_returns_false_for_unknown_theme(): void
    {
        $this->assertFalse(ThemeRegistry::exists('unknown_theme'));
    }

    public function test_default_returns_classic(): void
    {
        $this->assertSame('classic', ThemeRegistry::default());
    }

    public function test_resolve_returns_key_for_valid_theme(): void
    {
        $this->assertSame('classic', ThemeRegistry::resolve('classic'));
        $this->assertSame('lumina', ThemeRegistry::resolve('lumina'));
    }

    public function test_resolve_falls_back_to_default_for_invalid_key(): void
    {
        $this->assertSame('classic', ThemeRegistry::resolve('nonexistent'));
        $this->assertSame('classic', ThemeRegistry::resolve(''));
        $this->assertSame('classic', ThemeRegistry::resolve('CLASSIC')); // case-sensitive
    }
}
