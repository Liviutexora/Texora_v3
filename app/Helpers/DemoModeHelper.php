<?php

namespace App\Helpers;

class DemoModeHelper
{
    /**
     * Check if demo mode is enabled
     */
    public static function isEnabled(): bool
    {
        return config('demo.enabled', false);
    }

    /**
     * Check if an action is restricted in demo mode
     */
    public static function isRestricted(string $action): bool
    {
        if (!static::isEnabled()) {
            return false;
        }

        $restrictedActions = config('demo.restricted_actions', []);
        $allowedActions = config('demo.allowed_actions', []);

        // If action is explicitly allowed, it's not restricted
        if (in_array(strtolower($action), array_map('strtolower', $allowedActions))) {
            return false;
        }

        // Check if action is in restricted list
        return in_array(strtolower($action), array_map('strtolower', $restrictedActions));
    }

    /**
     * Check if a resource is excluded from demo mode restrictions
     */
    public static function isResourceExcluded(string $resourceClass): bool
    {
        if (!static::isEnabled()) {
            return false;
        }

        $excludedResources = config('demo.excluded_resources', []);
        return in_array($resourceClass, $excludedResources);
    }

    /**
     * Check if an action is allowed for a specific resource
     */
    public static function isActionAllowedForResource(string $resourceClass, string $action): bool
    {
        if (!static::isEnabled()) {
            return true;
        }

        $resourceAllowedActions = config('demo.resource_allowed_actions', []);
        
        if (isset($resourceAllowedActions[$resourceClass])) {
            return in_array(strtolower($action), array_map('strtolower', $resourceAllowedActions[$resourceClass]));
        }

        return false;
    }

    /**
     * Get the restricted message
     */
    public static function getRestrictedMessage(): string
    {
        return config('demo.restricted_message', 'This action is disabled in demo mode.');
    }

    /**
     * Get the banner message
     */
    public static function getBannerMessage(): string
    {
        return config('demo.banner_message', 'Demo Mode: Edit and delete operations are disabled.');
    }

    /**
     * Check if banner should be shown
     */
    public static function shouldShowBanner(): bool
    {
        return static::isEnabled() && config('demo.show_banner', true);
    }
}

