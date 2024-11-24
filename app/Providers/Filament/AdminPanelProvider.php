<?php

namespace App\Providers\Filament;

use App\Filament\Resources\AnagraficaResource;
use App\Filament\Resources\CategoriaDocumentoResource;
use App\Filament\Resources\DocumentoResource;
use App\Filament\Resources\NotaResource;
use App\Filament\Resources\PraticaResource;
use App\Filament\Resources\ScadenzaResource;
use App\Filament\Resources\UdienzaResource;
use App\Http\Middleware\CheckIfBanned;
use App\Models\Anagrafica;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\Middleware\SyncShieldTenant;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
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

// use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
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
            ->databaseNotifications()
            ->passwordReset()
            ->navigationItems([

                NavigationItem::make('Ruoli & Permessi')
                    ->visible(fn(): bool => auth()->user()->hasRole('super_admin'))
                    ->group('Amministrazione')
                    ->url('/admin/shield/roles'),


            ])
            ->navigationGroups([

                NavigationGroup::make()
                    ->label('Amministrazione')
                    ->icon('heroicon-o-cog')
                    ->items([

                    ]),

                // Gruppo di navigazione per la gestione delle pratiche
                NavigationGroup::make()
                    ->label('Gestione Pratiche')
                    ->icon('heroicon-o-briefcase')
                    ->items([
                        // Risorse relative alle pratiche
                        PraticaResource::class,
                        NotaResource::class
                    ]),


                // Gruppo di navigazione per la gestione dell'agenda
                NavigationGroup::make()
                    ->label('Agenda')
                    ->icon('heroicon-o-calendar')
                    ->items([
                        // Scadenze e udienze
                        ScadenzaResource::class,
                        UdienzaResource::class
                    ]),

                // Gruppo di navigazione per la gestione delle anagrafiche
                NavigationGroup::make()
                    ->label('Anagrafiche')
                    ->icon('heroicon-o-users')
                    ->items([
                        // Risorse relative alle anagrafiche
                        AnagraficaResource::class,

                    ]),

                // Gruppo di navigazione per la gestione dei documenti
                NavigationGroup::make()
                    ->label('Documenti')
                    ->icon('heroicon-o-document')
                    ->items([
                        // Risorse relative ai documenti
                        DocumentoResource::class,
                        CategoriaDocumentoResource::class
                    ]),

            ])
            ->resources([
                //config('filament-logger.activity_resource')
            ])
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([

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
                CheckIfBanned::class
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentUsersPlugin::make(),
                \DiscoveryDesign\FilamentGaze\FilamentGazePlugin::make(),
                //FilamentFullCalendarPlugin::make()->selectable()

            ])


            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
