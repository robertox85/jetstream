<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

trait HasTeamUsers
{
    /**
     * Ottieni tutti gli utenti associati ad un qualsiasi team (membri e proprietario)
     *
     * @param int|array|null $teamIds Se specificato, filtra per specifici team
     * @return mixed[]
     */
    public static function getAllTeamUsers(int|array|null $teamIds = null): array
    {
        $teamIds = (array) $teamIds;

        $query = User::query()
            ->distinct()
            ->where('is_banned', false)->orWhereNull('is_banned')
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Cliente')->orWhere('name', 'super_admin');
            })
            ->where(function ($query) use ($teamIds) {
                $query->whereHas('teams', function ($q) use ($teamIds) {
                    if (!empty($teamIds)) {
                        $q->whereIn('team_id', $teamIds);
                    }
                })
                    // O utenti che sono proprietari del team (teams table, user_id)
                    ->orWhereHas('ownedTeams', function ($q) use ($teamIds) {
                        if (!empty($teamIds)) {
                            $q->whereIn('id', $teamIds);
                        }
                    });


            });

        return $query->pluck('name', 'id')->toArray();

    }

    /**
     * Ottieni tutti gli utenti associati ad un team specifico
     *
     * @param int $teamId
     * @return Collection<User>
     */
    public static function getTeamUsers(int $teamId): Collection
    {
        return self::getAllTeamUsers($teamId);
    }
}