<?php

namespace App\Http\Middleware;

use Closure;


class RestrictPraticaFiles
{
    public function handle($request, Closure $next)
    {
        $pratica = $request->route('pratica');
        $user = $request->user();
        if ($user->pratiche->contains($pratica)) {
            return $next($request);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }
}