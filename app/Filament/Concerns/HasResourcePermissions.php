<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasResourcePermissions
{
    /**
     * Get the authenticated user, or null if not authenticated.
     */
    protected static function getAuthUser(): ?\App\Models\User
    {
        return Auth::user();
    }

    /**
     * Permission entity name used for this resource (e.g. "User", "Booking").
     * Override in resources that share a model but need separate permissions.
     */
    protected static function getPermissionEntityName(): string
    {
        return class_basename(static::$model);
    }

    /**
     * Check if the user has a permission for the given model.
     */
    protected static function checkPermission(string $action, string|Model $model): bool
    {
        if (!$user = static::getAuthUser()) {
            return false;
        }

        return $user->can("{$action}:" . static::getPermissionEntityName());
    }

    /**
     * Check if the user can view any records of the model.
     */
    public static function canViewAny(): bool
    {
        return static::checkPermission('ViewAny', static::$model);
    }

    /**
     * Check if the user can view a specific record.
     */
    public static function canView(Model $record): bool
    {
        return static::checkPermission('View', $record);
    }

    /**
     * Check if the user can create new records.
     */
    public static function canCreate(): bool
    {
        return static::checkPermission('Create', static::$model);
    }

    /**
     * Check if the user can edit a specific record.
     */
    public static function canEdit(Model $record): bool
    {
        return static::checkPermission('Update', $record);
    }

    /**
     * Check if the user can delete a specific record.
     */
    public static function canDelete(Model $record): bool
    {
        return static::checkPermission('Delete', $record);
    }

    /**
     * Check if the user can delete any records.
     */
    public static function canDeleteAny(): bool
    {
        return static::checkPermission('DeleteAny', static::$model);
    }
}

