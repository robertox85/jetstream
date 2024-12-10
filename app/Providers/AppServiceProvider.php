<?php

namespace App\Providers;

use App\Models\Evento;
use App\Observers\EventoObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\Permission;
use App\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionClass(Permission::class)
            ->setRoleClass(Role::class);
        //
        // Observe Evento model
        Evento::observe(EventoObserver::class);
    }

}
