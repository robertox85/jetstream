<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Entities con policy comuni
        \App\Models\Pratica::class => \App\Policies\PraticaPolicy::class,
        \App\Models\Anagrafica::class => \App\Policies\AnagraficaPolicy::class,
        \App\Models\Assistito::class => \App\Policies\AssistitoPolicy::class,
        \App\Models\Controparte::class => \App\Policies\ContropartePolicy::class,
        \App\Models\Nota::class => \App\Policies\NotaPolicy::class,
        \App\Models\Udienza::class => \App\Policies\UdienzaPolicy::class,
        \App\Models\Scadenza::class => \App\Policies\ScadenzaPolicy::class,

        // Entities con policy specifiche
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Team::class => \App\Policies\TeamPolicy::class,
        \App\Models\Documento::class => \App\Policies\DocumentoPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Puoi definire qui altri Gate personalizzati se necessario
        Gate::define('view-dashboard', function ($user) {
            return $user->hasAnyRole(['admin', 'coordinatore', 'avvocato']);
        });
    }
}