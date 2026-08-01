<?php

namespace App\Services\Pwa\View\Components;

use App\Services\Pwa\Contracts\PwaContextResolverInterface;
use App\Services\Pwa\Core\PwaMetaTagService;
use Illuminate\View\Component;
use Illuminate\View\View;

class PwaHead extends Component
{
    public function __construct(
        public ?string $appId = null,
        public ?string $startUrl = null,
        public ?string $scope = null,
    ) {
    }

    public function render(): View
    {
        $contextResolver = app(PwaContextResolverInterface::class);
        $context = $contextResolver->resolve(request())->with(
            $this->appId,
            $this->startUrl,
            $this->scope,
        );

        $meta = app(PwaMetaTagService::class)->build($context);

        return view('pwa::components.head', [
            'context' => $context,
            'meta' => $meta,
            'manifestUrl' => route('pwa.manifest', $context->manifestQuery()),
            'serviceWorkerUrl' => route('pwa.service-worker'),
            'versionUrl' => route('pwa.version'),
            'viteEntry' => config('pwa.vite_entry'),
            'checkIntervalMs' => (int) config('pwa.version.check_interval_ms', 300000),
            'iosInstructions' => (string) config('pwa.install.ios_instructions', ''),
        ]);
    }
}