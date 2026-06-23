<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class SitemapController extends Controller
{
    public function generate()
    {
        $baseUrl = config('app.url') ?: URL::to('/');

        $items = [];

        // Static public pages
        foreach (['', 'for-businesses', 'for-clients', 'contact'] as $uri) {
            $items[] = [
                'loc'        => rtrim($baseUrl, '/') . ($uri ? "/{$uri}" : '/'),
                'lastmod'    => Carbon::now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority'   => $uri === '' ? '1.0' : '0.6',
            ];
        }

        // Active tenant booking pages
        Tenant::where('status', 'active')
            ->select(['slug', 'updated_at'])
            ->orderBy('slug')
            ->each(function (Tenant $tenant) use ($baseUrl, &$items) {
                $items[] = [
                    'loc'        => rtrim($baseUrl, '/') . "/{$tenant->slug}",
                    'lastmod'    => Carbon::parse($tenant->updated_at)->toAtomString(),
                    'changefreq' => 'daily',
                    'priority'   => '0.8',
                ];
            });

        $xmlString = $this->buildSitemapXml($items);
        $path      = public_path('sitemap.xml');
        File::put($path, $xmlString);

        return response()->json([
            'message'    => 'Sitemap generated successfully',
            'path'       => $path,
            'urls_count' => count($items),
        ]);
    }

    protected function buildSitemapXml(array $items): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($items as $it) {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($it['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8'));
            $url->addChild('lastmod', $it['lastmod']);
            $url->addChild('changefreq', $it['changefreq']);
            $url->addChild('priority', $it['priority']);
        }

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }
}
