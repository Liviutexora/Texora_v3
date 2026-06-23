<?php

namespace App\View;

use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewFinderInterface;
use Illuminate\Support\Facades\Request;
use InvalidArgumentException;

class ThemeViewFinder extends FileViewFinder implements ViewFinderInterface
{
    protected ?string $activeTheme = null;
    protected ?string $defaultTheme = null;

    public function setThemes(string $activeTheme, string $defaultTheme): void
    {
        $this->activeTheme = $activeTheme;
        $this->defaultTheme = $defaultTheme;
    }

    public function find($name)
    {
        if ($this->isAdminOrSystemView($name)) {
            return parent::find($name);
        }

        try {
            return $this->findInTheme($this->activeTheme, $name);
        } catch (InvalidArgumentException $e) {
            try {
                return $this->findInTheme($this->defaultTheme, $name);
            } catch (InvalidArgumentException $e2) {
                // Fallback to Laravel's default resources/views directory
                return $this->findInResourcesViews($name);
            }
        }
    }

    protected function findInTheme(string $theme, string $name)
    {
        $themePath = base_path("themes/{$theme}/views");

        $paths = [$themePath];
        $this->setPaths($paths);

        return parent::find($name);
    }

    protected function findInResourcesViews(string $name)
    {
        // Use Laravel's default resources/views path
        $resourcesPath = resource_path('views');
        
        $paths = [$resourcesPath];
        $this->setPaths($paths);

        return parent::find($name);
    }

    protected function isAdminOrSystemView(string $name): bool
    {
        $path = Request::path();

        // ✅ If request is for admin, filament, or livewire
        if (str_starts_with($path, 'admin') || str_contains($name, 'filament') || str_contains($name, 'livewire')) {
            return true;
        }

        // ✅ Skip vendor/system namespaces
        $skipNamespaces = ['filament', 'livewire', 'auth', 'notifications', 'vendor'];
        foreach ($skipNamespaces as $ns) {
            if (str_starts_with($name, $ns . '::')) {
                return true;
            }
        }

        return false;
    }
}
