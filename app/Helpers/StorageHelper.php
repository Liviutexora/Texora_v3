<?php

use App\Helpers\ThemeHelper;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

if (!function_exists('storage_url')) {
    /**
     * Get storage URL for a file path using the configured disk
     * This function uses the cached disk name to avoid repeated queries
     * 
     * @param string $filePath The file path (e.g., 'images/photo.jpg' or 'storage/images/photo.jpg')
     * @return string The full URL to the file
     */
    function storage_url(?string $filePath = null): string
    {
        if (!$filePath) {
            return '';
        }
        // Remove 'storage/' prefix if present
        $filePath = preg_replace('#^storage/#', '', $filePath);
        
        // Get the disk name (cached)
        $disk = ThemeHelper::getFilesystemDisk();
        
        // Get the URL using Storage facade
        return Storage::disk($disk)->url($filePath);
    }
}


if (!function_exists('checkAndAssignUserRole')) {
    /**
     * Assign a sensible default role to any user that has none.
     *
     * Users who are linked to a provider record → staff.
     * Everyone else (booking guests who registered, etc.) → client.
     *
     * Called lazily from admin panel boot so it only runs when the
     * admin panel is loaded, not on every request.
     */
    function checkAndAssignUserRole(): void
    {
        $usersWithoutRole = User::doesntHave('roles')->get();
        if ($usersWithoutRole->isEmpty()) {
            return;
        }

        $staffRole  = Role::firstOrCreate(['name' => 'staff',  'guard_name' => 'web']);
        $clientRole = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);

        // Provider user IDs — these get staff; everyone else gets client
        $providerUserIds = \Illuminate\Support\Facades\DB::table('providers')
            ->pluck('user_id')
            ->flip()
            ->toArray();

        foreach ($usersWithoutRole as $user) {
            $user->assignRole(
                isset($providerUserIds[$user->id]) ? $staffRole : $clientRole
            );
        }
    }
}


if (!function_exists('placeholder_image')) {
    function placeholder_image(): string
    {
        return 'https://placehold.co/600x400';
    }
}
