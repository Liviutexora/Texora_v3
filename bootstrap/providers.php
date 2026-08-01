<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Services\Pwa\PwaCoreServiceProvider::class,
    App\Services\Pwa\Adapters\Texora\TexoraPwaServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\TenantPanelProvider::class,
    App\Providers\MiddlewareServiceProvider::class,
    App\Providers\UserActivityServiceProvider::class,
    ProjectsCore\ProjectsCoreServiceProvider::class,
    BinaryTorch\LaRecipe\LaRecipeServiceProvider::class,
];
