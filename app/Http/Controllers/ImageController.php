<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageController extends Controller
{
    /**
     * Domains permitted as image sources.
     * Add your CDN / S3 / storage domains here.
     */
    private const ALLOWED_HOSTS = [
        // Storage origins
        'amazonaws.com',
        'digitaloceanspaces.com',
        'cloudflare.com',
        'cloudinary.com',
        'imgix.net',
        // UI placeholder services used in demos
        'ui-avatars.com',
        'gravatar.com',
        'picsum.photos',
        'placehold.co',
        'via.placeholder.com',
    ];

    /**
     * Resize image from URL.
     */
    public function resize(Request $request, int $width, int $height)
    {
        // Validate dimensions
        if ($width < 1 || $width > 2000 || $height < 1 || $height > 2000) {
            abort(400, 'Invalid image dimensions. Maximum size is 2000×2000.');
        }

        $url = $request->query('url');
        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Valid URL parameter is required.');
        }

        // Validate URL scheme (only allow http/https)
        $parsed = parse_url($url);
        if (! in_array($parsed['scheme'] ?? '', ['http', 'https'], true)) {
            abort(400, 'Only HTTP and HTTPS URLs are allowed.');
        }

        // SSRF guard — host must match an allowed domain suffix
        $host = strtolower($parsed['host'] ?? '');
        $allowed = false;
        foreach (self::ALLOWED_HOSTS as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                $allowed = true;
                break;
            }
        }

        // Also allow the application's own storage URL
        $appHost = strtolower(parse_url(config('app.url'), PHP_URL_HOST) ?? '');
        if (! $allowed && $appHost && ($host === $appHost || str_ends_with($host, '.' . $appHost))) {
            $allowed = true;
        }

        if (! $allowed) {
            abort(403, 'Image host is not permitted.');
        }

        // Cache key for resized image
        $cacheKey = "img_resize_{$width}_{$height}_" . md5($url);

        try {
            $cachedImage = Cache::get($cacheKey);
            if ($cachedImage) {
                return response($cachedImage, 200)
                    ->header('Content-Type', 'image/jpeg')
                    ->header('Cache-Control', 'public, max-age=3600');
            }

            $imageData = Http::timeout(10)->get($url)->body();
            $manager   = new ImageManager(new Driver());
            $image     = $manager->read($imageData)->scaleDown($width, $height);
            $imageData = $image->toJpeg();

            Cache::put($cacheKey, $imageData, 3600);

            return response($imageData, 200)
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=3600');
        } catch (\Exception $e) {
            Log::error('Image resize failed', ['url' => $url, 'exception' => $e->getMessage()]);
            abort(500, 'Failed to process image.');
        }
    }
}
