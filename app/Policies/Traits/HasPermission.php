<?php

namespace App\Policies\Traits;

use App\Models\User;

trait HasPermission
{

    CONST ROLES = [
        'super_admin',
        'Amministratore',
    ];

    public function before(User $user, string $ability): bool|null
    {
        if ($user->is_banned) {
            return false;
        }
        // Super Admin e Admin hanno pieno controllo
        if ($user->hasAnyRole(self::ROLES)) {
            return true;
        }

        // Usa false se vuoi bloccare categoricamente l'accesso, senza eseguire altri controlli.
        // Se vuoi eseguire altri controlli, usa null.
        return null;
    }

    /**
     * Verifica se l'utente è associato a una pratica tramite il team.
     */
    public function isAssociatedWithPractice($practice): bool
    {
        return $practice->team->users->contains($this);
    }


}
