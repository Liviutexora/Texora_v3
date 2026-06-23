<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FontProxyController extends Controller
{
    /**
     * Proxy Google Fonts CSS server-side so the browser never touches fonts.googleapis.com.
     * This keeps all outbound CDN requests off the client, satisfying CodeCanyon requirements.
     *
     * The CSS response is cached for 24 hours per family.
     */
    public function __invoke(Request $request)
    {
        $family  = $request->query('family');
        $weights = $request->query('weights', '300;400;500;600;700;800');

        if (blank($family) || !preg_match('/^[\w\s,+]+$/', $family)) {
            return response('/* Invalid font family */', 400)->header('Content-Type', 'text/css');
        }

        $cacheKey = 'font_proxy_' . md5($family . $weights);

        $css = Cache::remember($cacheKey, now()->addHours(24), function () use ($family, $weights) {
            $familyEnc = str_replace(' ', '+', $family);
            $url       = "https://fonts.googleapis.com/css2?family={$familyEnc}:wght@{$weights}&display=swap";

            try {
                $response = Http::withHeaders([
                    // Use a modern UA so Google returns woff2 instead of ttf
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])->timeout(5)->get($url);

                return $response->successful() ? $response->body() : '/* Font load failed */';
            } catch (\Throwable) {
                return '/* Font load failed */';
            }
        });

        return response($css, 200)
            ->header('Content-Type', 'text/css; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
