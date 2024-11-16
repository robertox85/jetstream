<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Udienza;
use Illuminate\Auth\Access\HandlesAuthorization;

class UdienzaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_udienza');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Udienza $udienza): bool
    {
        return $user->can('view_udienza');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_udienza');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Udienza $udienza): bool
    {
        return $user->can('update_udienza');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Udienza $udienza): bool
    {
        return $user->can('delete_udienza');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_udienza');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Udienza $udienza): bool
    {
        return $user->can('force_delete_udienza');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_udienza');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Udienza $udienza): bool
    {
        return $user->can('restore_udienza');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_udienza');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Udienza $udienza): bool
    {
        return $user->can('replicate_udienza');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_udienza');
    }
}
