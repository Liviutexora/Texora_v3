<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ContactUs;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactUsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ContactUs');
    }

    public function view(AuthUser $authUser, ContactUs $contactUs): bool
    {
        return $authUser->can('View:ContactUs');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ContactUs');
    }

    public function update(AuthUser $authUser, ContactUs $contactUs): bool
    {
        return $authUser->can('Update:ContactUs');
    }

    public function delete(AuthUser $authUser, ContactUs $contactUs): bool
    {
        return $authUser->can('Delete:ContactUs');
    }

    public function restore(AuthUser $authUser, ContactUs $contactUs): bool
    {
        return $authUser->can('Restore:ContactUs');
    }

    public function forceDelete(AuthUser $authUser, ContactUs $contactUs): bool
    {
        return $authUser->can('ForceDelete:ContactUs');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ContactUs');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ContactUs');
    }

    public function replicate(AuthUser $authUser, ContactUs $contactUs): bool
    {
        return $authUser->can('Replicate:ContactUs');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ContactUs');
    }

}