<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ApiToken;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApiTokenPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ApiToken');
    }

    public function view(AuthUser $authUser, ApiToken $apiToken): bool
    {
        return $authUser->can('View:ApiToken');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ApiToken');
    }

    public function update(AuthUser $authUser, ApiToken $apiToken): bool
    {
        return $authUser->can('Update:ApiToken');
    }

    public function delete(AuthUser $authUser, ApiToken $apiToken): bool
    {
        return $authUser->can('Delete:ApiToken');
    }

    public function restore(AuthUser $authUser, ApiToken $apiToken): bool
    {
        return $authUser->can('Restore:ApiToken');
    }

    public function forceDelete(AuthUser $authUser, ApiToken $apiToken): bool
    {
        return $authUser->can('ForceDelete:ApiToken');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ApiToken');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ApiToken');
    }

    public function replicate(AuthUser $authUser, ApiToken $apiToken): bool
    {
        return $authUser->can('Replicate:ApiToken');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ApiToken');
    }

}