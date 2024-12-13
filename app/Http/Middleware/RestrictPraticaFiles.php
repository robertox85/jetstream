<?php

namespace App\Http\Middleware;

use App\Models\Pratica;
use Closure;


class RestrictPraticaFiles
{
    public function handle($request, Closure $next)
    {
        $pratica = $request->route('pratica');
        $user = $request->user();

         if ($user->hasRole('super_admin')) {
             return $next($request);
         }

         if ($user->hasRole('Amministratore')) {
             return $next($request);
         }

        // get pratica team
        $team = Pratica::find($pratica)->team;

        // check if user is in team
         if ($team->users->contains($user)) {
             return $next($request);
         }


        abort(403, ' Accesso negato. ');
    }
}