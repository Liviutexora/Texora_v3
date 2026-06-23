<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EmailTemplateLayout;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailTemplateLayoutPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EmailTemplateLayout');
    }

    public function view(AuthUser $authUser, EmailTemplateLayout $emailTemplateLayout): bool
    {
        return $authUser->can('View:EmailTemplateLayout');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EmailTemplateLayout');
    }

    public function update(AuthUser $authUser, EmailTemplateLayout $emailTemplateLayout): bool
    {
        return $authUser->can('Update:EmailTemplateLayout');
    }

    public function delete(AuthUser $authUser, EmailTemplateLayout $emailTemplateLayout): bool
    {
        return $authUser->can('Delete:EmailTemplateLayout');
    }

    public function restore(AuthUser $authUser, EmailTemplateLayout $emailTemplateLayout): bool
    {
        return $authUser->can('Restore:EmailTemplateLayout');
    }

    public function forceDelete(AuthUser $authUser, EmailTemplateLayout $emailTemplateLayout): bool
    {
        return $authUser->can('ForceDelete:EmailTemplateLayout');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EmailTemplateLayout');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EmailTemplateLayout');
    }

    public function replicate(AuthUser $authUser, EmailTemplateLayout $emailTemplateLayout): bool
    {
        return $authUser->can('Replicate:EmailTemplateLayout');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EmailTemplateLayout');
    }

}