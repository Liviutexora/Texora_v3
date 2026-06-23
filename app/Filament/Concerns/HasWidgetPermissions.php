<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Facades\Auth;

trait HasWidgetPermissions
{
    /**
     * Get the authenticated user, or null if not authenticated.
     */
    protected static function getAuthUser(): ?\App\Models\User
    {
        return Auth::user();
    }

    /**
     * Check if the user can view this widget.
     * Permission format: View:WidgetClassName
     *
     * Filament renders dashboard widgets only when this returns true (see
     * {@see \Filament\Pages\Page::getWidgetsSchemaComponents()}). New widgets do not
     * have Shield permissions until roles are regenerated/synced, so the configured
     * super admin role bypasses the check.
     */
    public static function canView(): bool
    {
        if (! $user = static::getAuthUser()) {
            return false;
        }

        if (config('filament-shield.super_admin.enabled', true)
            && $user->hasRole((string) config('filament-shield.super_admin.name', 'super_admin'))) {
            return true;
        }

        $widgetName = class_basename(static::class);

        return $user->can("View:{$widgetName}");
    }
}
