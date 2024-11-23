<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Team;
use App\Models\User;
use App\Traits\HasTeamAuthorizationScope;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Filament\Pages\Actions\Modal\Actions\ButtonAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Laravel\Jetstream\TeamInvitation;
use TomatoPHP\FilamentUsers\Resources\UserResource\Table\Actions\ImpersonateAction;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Utenti';

    protected static ?string $pluralModelLabel = 'Utenti';

    protected static ?string $slug = 'utenti';

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Amministrazione';


    use HasTeamAuthorizationScope;

    public static function getEloquentQuery(): Builder
    {
        return static::getRoleVisibilityQuery();
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Username')
                            ->required()
                            ->maxLength(255)
                        ,

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                        ,

                        Forms\Components\Select::make('roles')
                            ->label('Ruoli')
                            ->relationship('roles', 'name')
                            ->options(
                                function () {

                                    // if user is super_admin or Amministratore
                                    if (auth()->user()->hasRole('super_admin')) {
                                        return \App\Models\Role::all()->pluck('name', 'id');
                                    }

                                    // Exclude super_admin and Amministratore roles
                                    return \App\Models\Role::where('name', '!=', 'super_admin')->where('name', '!=', 'Amministratore')->get()->pluck('name', 'id');

                                }
                            )
                            ->multiple()
                            ->preload()
                            ->required()
                        ,


                        // Proprietario di un team
                        Forms\Components\Select::make('owner')
                            ->label('Proprietario di')
                            ->relationship('teams', 'name')
                            ->multiple()
                            ->preload(),


                        Forms\Components\Select::make('teams')
                            ->label('Gruppi')
                            ->relationship('teams', 'name')
                            ->multiple()
                            ->preload()
                        ,


                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->required(fn($livewire) => $livewire instanceof Pages\CreateUser)
                            ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => !empty($state)),


                        Forms\Components\Toggle::make('is_banned')
                            ->label('Disabilitato')
                            ->columnSpanFull()
                            ->helperText('Disabilita l\'utente')


                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')  // Ordinamento singolo
            ->paginated([100, 150, 'all'])
            ->defaultPaginationPageOption(100)
            // Personalizza l'azione di toggle
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Nome')
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                // Teams
                Tables\Columns\TextColumn::make('teams')
                    ->getStateUsing(fn($record) => self::getUserTeams($record))
                    ->label('Gruppi')
                    ->toggleable()
                ,

                // Model_has_ role
                Tables\Columns\TextColumn::make('roles.name')
                    ->getStateUsing(fn($record) => $record->roles->pluck('name')->join(', '))
                    ->badge()
                    ->color(fn($state) => self::getRoleColor($state))
                    ->icon('heroicon-o-shield-check')
                    ->label('Ruoli')
                    ->searchable()
                    ->toggleable()
                    ->sortable(query: fn(Builder $query, string $direction) => $query->orderByRoleImportance($direction)),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Verificato')
                    ->getStateUsing(fn($record) => $record->email_verified_at ? 'Sì' : 'No')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_banned')
                    ->label('Stato')
                    ->getStateUsing(fn($record) => self::getInvitationStatus($record))
                    ->color(fn($state) => self::getInvitationStatusColor($state))
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Ruoli')
                    ->preload() // Carica i dati iniziali
                    ->options(
                        function () {
                            // remove super_admin and Amministratore roles
                            return \App\Models\Role::where('name', '!=', 'super_admin')->where('name', '!=', 'Amministratore')->get()->pluck('name', 'name');
                        }
                    )
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['values'],
                            fn(Builder $query, array $values) => $query->whereHas(
                                'roles',
                                fn($query) => $query->whereIn('name', $values)
                            )
                        );
                    })
                    ->visible(fn() => auth()->user()->hasRole(['super_admin', 'Amministratore'])) // Visibile solo per super_admin
                    ->searchable()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('teams')
                    ->label('Gruppi')
                    ->preload() // Carica i dati iniziali
                    ->options(
                        function () {
                            $teams = Team::all()->pluck('name', 'name');

                            // add also empty value
                            $teams->prepend('Nessun gruppo', 'none');

                            return $teams;
                        }
                    )
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }

                        return $query->where(function ($query) use ($data) {
                            foreach ($data['values'] as $value) {
                                if ($value === 'none') {
                                    $query->orWhereDoesntHave('teams');
                                } else {
                                    $query->orWhereHas('teams', fn($q) => $q->where('name', $value));
                                }
                            }
                        });
                    })
                    ->searchable()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('is_banned')
                    ->label('Stato')
                    ->options([
                        'Attivo' => 'Attivo',
                        'Disabilitato' => 'Disabilitato',
                        'Da assegnare a un gruppo' => 'Da assegnare a un gruppo',
                        'In attesa di accettazione' => 'In attesa di accettazione',
                        'Non invitato' => 'Non invitato',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }

                        return $query->where(function ($query) use ($data) {
                            foreach ($data['values'] as $status) {
                                switch ($status) {
                                    case 'Disabilitato':
                                        $query->orWhere('is_banned', true);
                                        break;

                                    case 'Da assegnare a un gruppo':
                                        $query->orWhere(function ($q) {
                                            $q->whereDoesntHave('teams')
                                                ->whereDoesntHave('ownedTeams')
                                                ->whereDoesntHave('roles', function ($q) {
                                                    $q->whereIn('name', ['super_admin', 'Amministratore']);
                                                });
                                        });
                                        break;

                                    case 'In attesa di accettazione':
                                        $query->orWhereExists(function ($q) {
                                            $q->select(DB::raw(1))
                                                ->from('team_invitations')
                                                ->whereColumn('team_invitations.email', 'users.email');
                                        });
                                        break;

                                    case 'Attivo':
                                        $query->orWhere(function ($q) {
                                            $q->whereNotNull('email_verified_at')
                                                ->where('is_banned', false)
                                                ->where(function ($q) {
                                                    $q->whereHas('teams')
                                                        ->orWhereHas('ownedTeams')
                                                        ->orWhereHas('roles', function ($q) {
                                                            $q->whereIn('name', ['super_admin', 'Amministratore']);
                                                        });
                                                });
                                        });
                                        break;

                                    case 'Non invitato':
                                        $query->orWhere(function ($q) {
                                            $q->whereNull('email_verified_at')
                                                ->where('is_banned', false)
                                                ->whereNotExists(function ($sq) {
                                                    $sq->select(DB::raw(1))
                                                        ->from('team_invitations')
                                                        ->whereColumn('team_invitations.email', 'users.email');
                                                });
                                        });
                                        break;
                                }
                            }
                        });
                    })
                    ->searchable()
                    ->multiple(),

            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('')->size('md'),
                Tables\Actions\DeleteAction::make()->label('')->size('md'),
                ImpersonateAction::make()->label('')->size('md'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('send_invitation')
                        ->label('Invita')
                        ->icon('heroicon-o-envelope')
                        ->requiresConfirmation(fn($record) => TeamInvitation::where('email', $record->email)->exists())
                        ->modalHeading(fn($record) => TeamInvitation::where('email', $record->email)->exists()
                            ? 'Re-inviare l\'invito?'
                            : 'Inviare l\'invito?'
                        )
                        ->modalDescription(fn($record) => TeamInvitation::where('email', $record->email)->exists()
                            ? 'Questo annullerà l\'invito precedente e ne invierà uno nuovo.'
                            : 'Verrà inviato un invito email all\'utente.'
                        )
                        ->modalSubmitActionLabel('Conferma')
                        ->action(fn($record) => self::sendResetPasswordEmail($record))
                        ->visible(fn($record) => !$record->roles->contains('name', 'super_admin'))
                        ->disabled(fn($record) => $record->teams->count() == 0)
                    ,

                    Tables\Actions\Action::make('disable_user')
                        ->label('Disabilita')
                        ->icon('heroicon-o-no-symbol')
                        ->requiresConfirmation()
                        ->modalHeading('Disabilitare l\'utente?')
                        ->modalDescription('L\'utente non potrà più accedere al sistema.')
                        ->modalSubmitActionLabel('Conferma')
                        ->action(fn(User $record) => $record->setAttribute('is_banned', true)->save())
                        ->visible(fn($record) => $record->getAttribute('is_banned') == false)
                    ,
                    Tables\Actions\Action::make('enable_user')
                        ->label('Abilita')
                        ->icon('heroicon-o-check-circle')
                        ->tooltip('Abilita utente')
                        ->requiresConfirmation()
                        ->modalHeading('Abilitare l\'utente?')
                        ->modalDescription('L\'utente potrà accedere al sistema.')
                        ->modalSubmitActionLabel('Conferma')
                        ->action(fn(User $record) => $record->setAttribute('is_banned', false)->save())
                        ->visible(fn($record) => $record->getAttribute('is_banned') == true),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    static function sendResetPasswordEmail($record): void
    {
        try {
            $status = Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
                ['email' => $record->email],
                function (CanResetPassword $user, string $token): void {
                    if (!method_exists($user, 'notify')) {
                        $userClass = $user::class;

                        throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
                    }

                    $notification = new ResetPasswordNotification($token);
                    $notification->url = Filament::getResetPasswordUrl($token, $user);

                    $user->notify($notification);
                },
            );
        } catch (Exception $e) {
            Log::error('Errore invio invito: ' . $e->getMessage());
        }

    }

    static function getInvitationStatus($record): string
    {
        // IF admin or super_admin
       if ($record->roles->contains('name', 'super_admin') || $record->roles->contains('name', 'Amministratore')) {
           return '';
       }


       if ($record->is_banned) {
           return 'Utente disabilitato';
       }

       // // is not Owner of any team
       if ($record->teams->count() == 0 && $record->ownedTeams->count() == 0) {
           return 'Da assegnare a un gruppo';
       }

       if (TeamInvitation::where('email', $record->email)->exists()) {
              return 'In attesa di accettazione';
       }

       if ($record->email_verified_at) {
           return 'Attivo';
       }

       return 'Non invitato';
    }

    static function getInvitationStatusColor($value): string
    {
        if ($value === 'Attivo') {
            return 'success';
        }

        if ($value === 'Disabilitato') {
            return 'danger';
        }

        if ($value === 'Da assegnare a un gruppo') {
            return 'warning';
        }

        if ($value === 'In attesa') {
            return 'info';
        }

        return 'secondary';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    private static function getRoleColor($state): string
    {
        if ($state === 'Amministratore' || $state === 'super_admin') {
            return 'success';
        }

        if ($state === 'Coordinatore') {
            return 'info';
        }

        // If String contains 'user'
        if (strpos($state, 'Amministratore') !== false) {
            return 'success';
        }


        return 'warning';
    }

    private static function getUserTeams($record)
    {
        // Recupera i team di cui è owner e quelli di cui è membro
        $ownedTeams = $record->ownedTeams->pluck('name')->join(', ');
        $memberTeams = $record->teams->pluck('name')->join(', ');

        // Costruisci il risultato basato sul ruolo
        if ($ownedTeams) {
            return $ownedTeams . ' (Proprietario)' . ($memberTeams ? ' | ' . $memberTeams : '');
        }

        return $memberTeams ?: 'Nessun gruppo';

    }
}
