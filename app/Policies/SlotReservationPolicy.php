<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SlotReservation;
use Illuminate\Auth\Access\HandlesAuthorization;

class SlotReservationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SlotReservation');
    }

    public function view(AuthUser $authUser, SlotReservation $slotReservation): bool
    {
        return $authUser->can('View:SlotReservation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SlotReservation');
    }

    public function update(AuthUser $authUser, SlotReservation $slotReservation): bool
    {
        return $authUser->can('Update:SlotReservation');
    }

    public function delete(AuthUser $authUser, SlotReservation $slotReservation): bool
    {
        return $authUser->can('Delete:SlotReservation');
    }

    public function restore(AuthUser $authUser, SlotReservation $slotReservation): bool
    {
        return $authUser->can('Restore:SlotReservation');
    }

    public function forceDelete(AuthUser $authUser, SlotReservation $slotReservation): bool
    {
        return $authUser->can('ForceDelete:SlotReservation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SlotReservation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SlotReservation');
    }

    public function replicate(AuthUser $authUser, SlotReservation $slotReservation): bool
    {
        return $authUser->can('Replicate:SlotReservation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SlotReservation');
    }

}