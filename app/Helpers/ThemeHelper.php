<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class ThemeHelper
{
    public static function getActiveTheme(): ?object
    {
        return (object) ['slug' => 'default', 'name' => 'Default'];
    }

    public static function getActiveThemeSettings(): array
    {
        try {
            if (! file_exists(base_path('.installed')) || ! Schema::hasTable('settings')) {
                return [];
            }
            return Setting::all()->pluck('value', 'key')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function getFilesystemDisk(): string
    {
        try {
            return Setting::get('filesystem_disk', env('FILESYSTEM_DISK', 'public')) ?? 'public';
        } catch (\Throwable $e) {
            return env('FILESYSTEM_DISK', 'public');
        }
    }

    public static function getSiteLogoUrl(): ?string
    {
        try {
            return Setting::get('site_logo') ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function getSiteFaviconUrl(): ?string
    {
        try {
            return Setting::get('site_favicon') ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function getFooterLinks(): array
    {
        return [];
    }

    public static function getSlides(): array
    {
        return [];
    }

    public static function formatSocialUrl(?string $url, string $platform = ''): ?string
    {
        if (empty($url)) {
            return null;
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        $bases = [
            'facebook'  => 'https://facebook.com/',
            'twitter'   => 'https://twitter.com/',
            'instagram' => 'https://instagram.com/',
            'linkedin'  => 'https://linkedin.com/in/',
            'youtube'   => 'https://youtube.com/@',
            'github'    => 'https://github.com/',
            'tiktok'    => 'https://tiktok.com/@',
            'pinterest' => 'https://pinterest.com/',
        ];
        $base = $bases[$platform] ?? 'https://';
        return $base . ltrim($url, '/');
    }

    public static function getDynamicHtmlLayout(string $html): string
    {
        return $html;
    }

    public static function reliable_asset(string $path): string
    {
        return asset($path);
    }
}
