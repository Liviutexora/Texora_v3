<?php

namespace App\Filament\Concerns;

use App\Helpers\DemoModeHelper;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;

trait HasDemoMode
{
    /**
     * Disable action in demo mode
     */
    protected function disableInDemoMode(Action $action, string $actionName = null): Action
    {
        if (DemoModeHelper::isEnabled()) {
            $resourceClass = static::class;
            
            // Check if resource is excluded
            if (DemoModeHelper::isResourceExcluded($resourceClass)) {
                return $action;
            }

            // Check if action is allowed for this resource
            if ($actionName && DemoModeHelper::isActionAllowedForResource($resourceClass, $actionName)) {
                return $action;
            }

            // Disable the action
            $action->disabled(true)
                ->tooltip(DemoModeHelper::getRestrictedMessage())
                ->color('gray');
        }

        return $action;
    }

    /**
     * Create a disabled edit action for demo mode
     */
    protected function createEditAction(): EditAction
    {
        $action = EditAction::make();
        
        if (DemoModeHelper::isEnabled() && !DemoModeHelper::isResourceExcluded(static::class)) {
            if (!DemoModeHelper::isActionAllowedForResource(static::class, 'edit')) {
                $action->disabled(true)
                    ->tooltip(DemoModeHelper::getRestrictedMessage())
                    ->color('gray');
            }
        }

        return $action;
    }

    /**
     * Create a disabled delete action for demo mode
     */
    protected function createDeleteAction(): DeleteAction
    {
        $action = DeleteAction::make();
        
        if (DemoModeHelper::isEnabled() && !DemoModeHelper::isResourceExcluded(static::class)) {
            if (!DemoModeHelper::isActionAllowedForResource(static::class, 'delete')) {
                $action->disabled(true)
                    ->tooltip(DemoModeHelper::getRestrictedMessage())
                    ->color('gray')
                    ->requiresConfirmation(false); // Disable confirmation since action is disabled
            }
        }

        return $action;
    }

    /**
     * Create a disabled create action for demo mode
     */
    protected function createCreateAction(): CreateAction
    {
        $action = CreateAction::make();
        
        if (DemoModeHelper::isEnabled() && !DemoModeHelper::isResourceExcluded(static::class)) {
            if (!DemoModeHelper::isActionAllowedForResource(static::class, 'create')) {
                $action->disabled(true)
                    ->tooltip(DemoModeHelper::getRestrictedMessage())
                    ->color('gray');
            }
        }

        return $action;
    }

    /**
     * Create a disabled bulk delete action for demo mode
     */
    protected function createDeleteBulkAction(): DeleteBulkAction
    {
        $action = DeleteBulkAction::make();
        
        if (DemoModeHelper::isEnabled() && !DemoModeHelper::isResourceExcluded(static::class)) {
            if (!DemoModeHelper::isActionAllowedForResource(static::class, 'delete')) {
                $action->disabled(true)
                    ->tooltip(DemoModeHelper::getRestrictedMessage())
                    ->color('gray');
            }
        }

        return $action;
    }

    /**
     * Show notification if action is restricted
     */
    protected function showDemoModeNotification(): void
    {
        if (DemoModeHelper::isEnabled()) {
            Notification::make()
                ->title(__('Demo Mode'))
                ->body(DemoModeHelper::getRestrictedMessage())
                ->warning()
                ->persistent()
                ->send();
        }
    }
}

