<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LoginActivity;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoginActivityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LoginActivity');
    }

    public function view(AuthUser $authUser, LoginActivity $loginActivity): bool
    {
        return $authUser->can('View:LoginActivity');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LoginActivity');
    }

    public function update(AuthUser $authUser, LoginActivity $loginActivity): bool
    {
        return $authUser->can('Update:LoginActivity');
    }

    public function delete(AuthUser $authUser, LoginActivity $loginActivity): bool
    {
        return $authUser->can('Delete:LoginActivity');
    }

    public function restore(AuthUser $authUser, LoginActivity $loginActivity): bool
    {
        return $authUser->can('Restore:LoginActivity');
    }

    public function forceDelete(AuthUser $authUser, LoginActivity $loginActivity): bool
    {
        return $authUser->can('ForceDelete:LoginActivity');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LoginActivity');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LoginActivity');
    }

    public function replicate(AuthUser $authUser, LoginActivity $loginActivity): bool
    {
        return $authUser->can('Replicate:LoginActivity');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LoginActivity');
    }

}