<?php

namespace App\Traits;

use App\Models\AnagraficaPratica;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Pratica;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

trait HasTeamAuthorizationScope
{

    /**
     * Helper per ottenere i team IDs dell'utente corrente
     */
    protected static function getUserTeamIds(): Collection
    {
        $user = auth()->user();

        if ($user->hasRole('Coordinatore')) {
            return $user->ownedTeams->pluck('id')
                ->merge($user->teams->pluck('id'))
                ->unique();
        }

        return $user->teams->pluck('id');
    }

    /**
     * Helper per verificare se l'utente ha accesso ad una pratica
     */
    public static function canAccessPratica(int $praticaId): bool
    {
        $user = auth()->user();

        if ($user->hasRole(['super_admin', 'Amministratore'])) {
            return true;
        }

        $pratica = Pratica::find($praticaId);
        return self::getUserTeamIds()->contains($pratica->team_id);
    }

    /**
     * Helper per verificare se l'utente ha accesso ad un'anagrafica
     */
    public static function canAccessAnagrafica(int $anagraficaId): bool
    {
        $user = auth()->user();

        if ($user->hasRole(['super_admin', 'Amministratore'])) {
            return true;
        }

        $praticaIds = AnagraficaPratica::where('anagrafica_id', $anagraficaId)
            ->pluck('pratica_id');

        return Pratica::whereIn('id', $praticaIds)
            ->whereIn('team_id', self::getUserTeamIds())
            ->exists();
    }

    /**
     * Helper per ottenere tutte le pratiche accessibili all'utente
     */
    public static function getAccessiblePratiche(): Collection
    {
        $user = auth()->user();

        if ($user->hasRole(['super_admin', 'Amministratore'])) {
            return Pratica::all();
        }

        return Pratica::whereIn('team_id', self::getUserTeamIds())->get();
    }

    /**
     * Filtra i record in base all'appartenenza ai team dell'utente autenticato
     *
     * Casi d'uso:
     * - Visualizzazione pratiche per team
     * - Filtering documenti per team
     * - Accesso a risorse team-based
     *
     * Logica:
     * - Super admin e Amministratori vedono tutto
     * - Coordinatori vedono i team di cui sono owner o membri
     * - Avvocati e Clienti vedono solo i team di cui sono membri
     */
    public static function getTeamBasedQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);

        if ($user->hasRole(['super_admin', 'Amministratore'])) {
            return $query;
        }

        if ($user->hasRole('Coordinatore')) {
            $teamIds = $user->ownedTeams->pluck('id')
                ->merge($user->teams->pluck('id'))
                ->unique();

            return $query->whereIn('team_id', $teamIds);
        }

        return $query->whereIn('team_id', $user->teams->pluck('id'));
    }

    /**
     * Filtra i record in base alle pratiche associate ai team dell'utente
     *
     * Casi d'uso:
     * - Visualizzazione note legate alle pratiche
     * - Accesso documenti di pratiche
     * - Filtering controparti/assistiti per pratica
     *
     * Logica:
     * - Super admin e Amministratori vedono tutto
     * - Altri ruoli vedono solo pratiche dei loro team
     */
    public static function getPraticaBasedQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->hasRole(['super_admin', 'Amministratore'])) {
            return $query;
        }

        $teamIds = $user->hasRole('Coordinatore')
            ? $user->ownedTeams->pluck('id')->merge($user->teams->pluck('id'))
            : $user->teams->pluck('id');

        $praticaIds = Pratica::whereIn('team_id', $teamIds)->pluck('id');
        return $query->whereIn('pratica_id', $praticaIds);
    }

    /**
     * Aggiunge filtri di visibilità pubblica/privata
     *
     * Casi d'uso:
     * - Filtering note private/pubbliche
     * - Visibilità documenti riservati
     *
     * Logica:
     * - Admin e Coordinatori vedono tutto
     * - Avvocati vedono contenuti pubblici + i propri privati
     * - Clienti vedono solo contenuti pubblici
     *
     * @param Builder $query Query base da filtrare
     * @param bool $considerOwnership Se true, gli avvocati vedono anche i propri contenuti privati
     */
    public static function addVisibilityScope(Builder $query, bool $considerOwnership = false): Builder
    {
        $user = auth()->user();

        if ($user->hasRole(['super_admin', 'Amministratore', 'Coordinatore'])) {
            return $query;
        }

        if ($considerOwnership && $user->hasRole('Avvocato')) {
            return $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('visibilita', 'pubblica');
            });
        }

        return $query->where('visibilita', 'pubblica');
    }

    /**
     * Filtra i record in base alle anagrafiche associate alle pratiche dei team dell'utente
     *
     * Casi d'uso:
     * - Visualizzazione udienze legate alle anagrafiche
     * - Accesso documenti di anagrafiche
     * - Filtering controparti/assistiti per anagrafica
     *
     * Logica:
     * - Super admin e Amministratori vedono tutto
     * - Altri ruoli vedono solo anagrafiche delle pratiche dei loro team
     */
    protected static function getAnagraficaBasedQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->hasRole(['super_admin', 'Amministratore'])) {
            return $query;
        }

        $teamIds = $user->hasRole('Coordinatore')
            ? $user->ownedTeams->pluck('id')->merge($user->teams->pluck('id'))
            : $user->teams->pluck('id');

        $anagraficaIds = AnagraficaPratica::whereIn('pratica_id',
            Pratica::whereIn('team_id', $teamIds)->pluck('id')
        )->pluck('anagrafica_id');

        return $query->whereIn('id', $anagraficaIds);
    }

    /**
     * Filtra i team in base all'appartenenza dell'utente
     * Mostra solo i team di cui l'utente è owner o membro
     */
    public static function getTeamVisibilityQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->hasRole(['super_admin', 'Amministratore'])) {
            return $query;
        }

        $teamIds = $user->hasRole('Coordinatore')
            ? $user->ownedTeams->pluck('id')->merge($user->teams->pluck('id'))
            : $user->teams->pluck('id');

        return $query->whereIn('id', $teamIds);
    }

    /**
     * Filtra gli utenti in base al ruolo
     * Mostra solo gli utenti con ruoli inferior
     */
    public static function getRoleVisibilityQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();



        // Super admin view all users
        if ($user->hasRole(['super_admin'])) {

            return $query;
        }

        // Admin view all users except super admin
        if ($user->hasRole(['Amministratore'])) {
            return $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super_admin');
            });
        }

        // Coordinatore view all users except super admin and admin
        if ($user->hasRole(['Coordinatore'])) {
            return $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super_admin');
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Amministratore');
            });
        }

        // Avvocato view all users except super admin, admin and coordinatore
        if ($user->hasRole(['Avvocato'])) {
            return $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super_admin');
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Amministratore');
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Coordinatore');
            });
        }

        // Cliente view all users except super admin, admin, coordinatore and avvocato
        if ($user->hasRole(['Cliente'])) {
            return $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super_admin');
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Amministratore');
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Coordinatore');
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Avvocato');
            });
        }

        return $query;
    }
}