<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pratica;
use Illuminate\Auth\Access\HandlesAuthorization;

class PraticaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_pratica');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pratica $pratica): bool
    {
        // return $user->can('view_pratica');

        // Permesso pieno per admin
        if ($user->hasRole(['super_admin', 'Amministratore'])) {
            return true;
        }

        // Permesso pieno per membri del team
        if ($pratica->team_id && $user->teams->contains($pratica->team_id)) {
            return true;
        }

        // Permesso di sola lettura tramite pratiche_utenti
        return $user->praticheInSolaLettura()
            ->where('pratica_id', $pratica->id)
            ->where('permission_type', 'read')
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {

        return $user->can('create_pratica');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pratica $pratica)
    {

        return $user->can('update_pratica');

        // Solo admin e membri del team possono modificare
       // if ($user->hasRole(['super_admin', 'Amministratore'])) {
       //     return true;
       // }

       // return $pratica->team_id && $user->teams->contains($pratica->team_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pratica $pratica): bool
    {

        // Solo admin e membri del team possono cancellare
        if ($user->hasRole(['super_admin', 'Amministratore'])) {
            return true;
        }

        return $user->can('delete_pratica');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_pratica');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Pratica $pratica): bool
    {
        return $user->can('force_delete_pratica');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_pratica');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Pratica $pratica): bool
    {
        return $user->can('restore_pratica');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_pratica');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Pratica $pratica): bool
    {
        return $user->can('replicate_pratica');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_pratica');
    }
}
