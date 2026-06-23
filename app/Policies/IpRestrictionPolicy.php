<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\IpRestriction;
use Illuminate\Auth\Access\HandlesAuthorization;

class IpRestrictionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:IpRestriction');
    }

    public function view(AuthUser $authUser, IpRestriction $ipRestriction): bool
    {
        return $authUser->can('View:IpRestriction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:IpRestriction');
    }

    public function update(AuthUser $authUser, IpRestriction $ipRestriction): bool
    {
        return $authUser->can('Update:IpRestriction');
    }

    public function delete(AuthUser $authUser, IpRestriction $ipRestriction): bool
    {
        return $authUser->can('Delete:IpRestriction');
    }

    public function restore(AuthUser $authUser, IpRestriction $ipRestriction): bool
    {
        return $authUser->can('Restore:IpRestriction');
    }

    public function forceDelete(AuthUser $authUser, IpRestriction $ipRestriction): bool
    {
        return $authUser->can('ForceDelete:IpRestriction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:IpRestriction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:IpRestriction');
    }

    public function replicate(AuthUser $authUser, IpRestriction $ipRestriction): bool
    {
        return $authUser->can('Replicate:IpRestriction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:IpRestriction');
    }

}