<?php

namespace App\Services\Pwa\Core;

use App\Services\Pwa\DTO\PwaBrandingData;

class PwaIconFallbackResolver
{
    public function manifestIcons(array $iconUrls): array
    {
        return [
            [
                'src' => $iconUrls['any_192'],
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => $iconUrls['any_512'],
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => $iconUrls['maskable_512'],
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
        ];
    }

    public function sourceCandidates(PwaBrandingData $branding, string $purpose, int $size): array
    {
        $candidates = match ([$purpose, $size]) {
            ['any', 192] => [
                $branding->icon192Url,
                $branding->faviconUrl,
                $branding->logoUrl,
                $branding->icon512Url,
                $branding->maskableIcon512Url,
            ],
            ['any', 512] => [
                $branding->icon512Url,
                $branding->logoUrl,
                $branding->faviconUrl,
                $branding->icon192Url,
                $branding->maskableIcon512Url,
            ],
            ['maskable', 512] => [
                $branding->maskableIcon512Url,
                $branding->icon512Url,
                $branding->logoUrl,
                $branding->faviconUrl,
                $branding->icon192Url,
            ],
            default => [],
        };

        return array_values(array_unique(array_filter($candidates)));
    }

    public function renderPng(array $candidateUrls, int $size, bool $maskable, string $label): string
    {
        foreach ($candidateUrls as $url) {
            $binary = $this->fetchBinary($url);
            if (! $binary) {
                continue;
            }

            $image = @imagecreatefromstring($binary);
            if (! $image) {
                continue;
            }

            try {
                return $this->toSizedPng($image, $size, $maskable);
            } finally {
                imagedestroy($image);
            }
        }

        return $this->placeholderPng($size, $label, $maskable);
    }

    private function fetchBinary(string $url): ?string
    {
        if (preg_match('/^https?:\/\//i', $url) === 1) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 2,
                    'follow_location' => 1,
                ],
            ]);

            $data = @file_get_contents($url, false, $ctx);
            if ($data !== false) {
                return $data;
            }

            $path = parse_url($url, PHP_URL_PATH);
            if (is_string($path) && $path !== '') {
                $localPath = public_path(ltrim($path, '/'));
                if (is_file($localPath)) {
                    return @file_get_contents($localPath) ?: null;
                }
            }

            return null;
        }

        $path = public_path(ltrim($url, '/'));
        if (! is_file($path)) {
            return null;
        }

        $data = @file_get_contents($path);

        return $data === false ? null : $data;
    }

    private function toSizedPng(\GdImage $source, int $size, bool $maskable): string
    {
        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $target = $maskable ? (int) floor($size * 0.8) : $size;
        $x = (int) floor(($size - $target) / 2);
        $y = (int) floor(($size - $target) / 2);

        imagecopyresampled($canvas, $source, $x, $y, 0, 0, $target, $target, $srcW, $srcH);

        ob_start();
        imagepng($canvas, null, 9);
        $png = (string) ob_get_clean();
        imagedestroy($canvas);

        return $png;
    }

    private function placeholderPng(int $size, string $label, bool $maskable): string
    {
        $theme = $this->hexToRgb((string) config('pwa.defaults.theme_color', '#111827'));
        $bg = $this->hexToRgb((string) config('pwa.defaults.background_color', '#ffffff'));

        $canvas = imagecreatetruecolor($size, $size);
        $bgColor = imagecolorallocate($canvas, $bg[0], $bg[1], $bg[2]);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $bgColor);

        $pad = $maskable ? (int) floor($size * 0.12) : (int) floor($size * 0.06);
        $tileColor = imagecolorallocate($canvas, $theme[0], $theme[1], $theme[2]);
        imagefilledrectangle($canvas, $pad, $pad, $size - $pad, $size - $pad, $tileColor);

        $initial = strtoupper(substr(trim($label) ?: 'A', 0, 1));
        $textColor = imagecolorallocate($canvas, 255, 255, 255);
        $font = max(2, min(5, (int) floor($size / 120) + 2));
        $textW = imagefontwidth($font) * strlen($initial);
        $textH = imagefontheight($font);
        $textX = (int) floor(($size - $textW) / 2);
        $textY = (int) floor(($size - $textH) / 2);
        imagestring($canvas, $font, $textX, $textY, $initial, $textColor);

        ob_start();
        imagepng($canvas, null, 9);
        $png = (string) ob_get_clean();
        imagedestroy($canvas);

        return $png;
    }

    private function hexToRgb(string $hex): array
    {
        $value = ltrim(trim($hex), '#');
        if (strlen($value) === 3) {
            $value = $value[0] . $value[0] . $value[1] . $value[1] . $value[2] . $value[2];
        }

        if (strlen($value) !== 6 || ! ctype_xdigit($value)) {
            return [17, 24, 39];
        }

        return [
            hexdec(substr($value, 0, 2)),
            hexdec(substr($value, 2, 2)),
            hexdec(substr($value, 4, 2)),
        ];
    }
}
