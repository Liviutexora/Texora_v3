<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\TenantPanelProvider::class,
    App\Providers\MiddlewareServiceProvider::class,
    App\Providers\UserActivityServiceProvider::class,
    ProjectsCore\ProjectsCoreServiceProvider::class,
    BinaryTorch\LaRecipe\LaRecipeServiceProvider::class,
];
