<?php

namespace App\Providers\Filament;

use App\Filament\Resources\AnagraficaResource;
use App\Filament\Resources\CategoriaDocumentoResource;
use App\Filament\Resources\DocumentoResource;
use App\Filament\Resources\NotaResource;
use App\Filament\Resources\PraticaResource;
use App\Filament\Resources\ScadenzaResource;
use App\Filament\Resources\UdienzaResource;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\Middleware\SyncShieldTenant;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use TomatoPHP\FilamentUsers\FilamentUsersPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->passwordReset()
            ->navigationGroups([

                NavigationGroup::make()
                    ->label('Gestione Pratiche')
                    ->icon('heroicon-o-briefcase')
                    ->items([
                        // Risorse relative alle pratiche
                        PraticaResource::class,
                        NotaResource::class
                    ]),

                NavigationGroup::make()
                    ->label('Agenda')
                    ->icon('heroicon-o-calendar')
                    ->items([
                        // Scadenze e udienze
                        ScadenzaResource::class,
                        UdienzaResource::class
                    ]),

                NavigationGroup::make()
                    ->label('Anagrafiche')
                    ->icon('heroicon-o-users')
                    ->items([
                        // Risorse relative alle anagrafiche
                        AnagraficaResource::class,

                    ]),

                NavigationGroup::make()
                    ->label('Documenti')
                    ->icon('heroicon-o-document')
                    ->items([
                        // Risorse relative ai documenti
                        DocumentoResource::class,
                        CategoriaDocumentoResource::class
                    ]),

            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentUsersPlugin::make()
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
