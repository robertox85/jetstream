<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckIfBanned
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('CheckIfBanned middleware is running');

        if (Auth::check() && Auth::user()->is_banned) {

            Auth::logout();

            Notification::make()
                ->title('Non puoi accedere')
                ->danger()
                ->send();

            return redirect()->route('filament.admin.auth.login');
        }

        return $next($request);
    }
}
