<?php

namespace App\Services\Pwa\Adapters\Texora;

use App\Services\Pwa\Contracts\PwaContextResolverInterface;
use App\Services\Pwa\DTO\PwaContextData;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TexoraPwaContextResolver implements PwaContextResolverInterface
{
    public function resolve(?Request $request = null): PwaContextData
    {
        $request ??= request();

        $overrideAppId = trim((string) $request->query('app_id', ''));
        $overrideStartUrl = trim((string) $request->query('start_url', ''));
        $overrideScope = trim((string) $request->query('scope', ''));

        if ($overrideAppId !== '' && $overrideStartUrl !== '' && $overrideScope !== '') {
            return new PwaContextData(
                appId: $overrideAppId,
                startUrl: $overrideStartUrl,
                scope: $overrideScope,
            );
        }

        $currentUrl = $request->url();
        $tenant = TenantContext::current();
        $path = '/' . ltrim($request->path(), '/');
        $normalizedPath = $path === '//' ? '/' : $path;

        if ($tenant && ($request->routeIs('booking.*') || Str::startsWith($normalizedPath, '/' . $tenant->slug))) {
            $scope = url('/' . trim($tenant->slug, '/') . '/');

            return new PwaContextData(
                appId: 'booking:' . $tenant->slug,
                startUrl: $currentUrl,
                scope: $scope,
            );
        }

        if (Str::startsWith($normalizedPath, '/manage')) {
            return new PwaContextData(
                appId: 'panel:tenant',
                startUrl: url('/manage'),
                scope: url('/manage/'),
            );
        }

        if (Str::startsWith($normalizedPath, '/admin')) {
            return new PwaContextData(
                appId: 'panel:admin',
                startUrl: url('/admin'),
                scope: url('/admin/'),
            );
        }

        return new PwaContextData(
            appId: 'site:default',
            startUrl: $currentUrl,
            scope: url('/'),
        );
    }
}