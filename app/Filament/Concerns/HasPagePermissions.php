<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Facades\Auth;

trait HasPagePermissions
{
    /**
     * Get the authenticated user, or null if not authenticated.
     */
    protected static function getAuthUser(): ?\App\Models\User
    {
        return Auth::user();
    }

    /**
     * Check if the user has permission to access this page.
     * Permission format: View:PageClassName
     */
    public static function canAccess(): bool
    {
        if (!$user = static::getAuthUser()) {
            return false;
        }

        $pageName = class_basename(static::class);
        return $user->can("View:{$pageName}");
    }
}

