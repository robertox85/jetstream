<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GruppoResource\Pages;
use App\Filament\Resources\GruppoResource\RelationManagers;
use App\Models\Gruppo;
use App\Models\Team;
use App\Traits\HasTeamAuthorizationScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GruppoResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $modelLabel = 'Gruppo';
    protected static ?string $pluralModelLabel = 'Gruppi';

    protected static ?string $slug = 'gruppi';
    protected static ?string $navigationGroup = 'Gestione Pratiche';

    protected static ?int $navigationSort = 2;


    use HasTeamAuthorizationScope;



    public static function getEloquentQuery(): Builder
    {
        return static::getTeamVisibilityQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome Gruppo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan('full'),

                        Forms\Components\Select::make('user_id')
                            ->label('Responsabile')
                            ->required()
                            ->relationship(
                                'users',
                                'name',
                                modifyQueryUsing: function (Builder $query) {
                                    $query->whereHas('roles', function ($query) {
                                        $query->whereIn('name', ['Coordinatore', 'Amministratore', 'Avvocato']);
                                    });
                                }
                            )
                            ->getOptionLabelUsing(fn($value) => \App\Models\User::find($value)?->name) // Recupera il nome utente per l'opzione selezionata
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ])
                        ,

                        Forms\Components\Select::make('users')
                            ->label('Membri')
                            ->multiple()
                            ->relationship('users', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')  // Ordinamento singolo
            ->paginated([100, 150, 'all'])
            ->defaultPaginationPageOption(100)
            ->columns([


                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('responsabile')
                    ->label('Responsabile')
                    ->toggleable()
                    ->getStateUsing(function ($record): string {
                        if (!$record->user_id) {
                            return 'N/D';
                        }

                        // Cache the user to avoid multiple queries
                        static $users = [];

                        if (!isset($users[$record->user_id])) {
                            $users[$record->user_id] = \App\Models\User::find($record->user_id);
                        }

                        return $users[$record->user_id]?->name ?? 'N/D';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('owner', fn($q) => $q->where('name', 'like', "%{$search}%")
                        );
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('user_id', $direction);
                    }),

                Tables\Columns\TextColumn::make('pratiche.nome')
                    ->label('Pratiche')
                    ->searchable()
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderByPraticheCount($direction))
                    ->toggleable()
                    ->badge()
                    ->getStateUsing(function ($record): string {
                        return $record->pratiche->count();
                    }),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make(),

            ])
            ->actions([

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Model $record) {
                        // Verifica se ci sono pratiche associate

                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                ]),
            ]);
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
            'index' => Pages\ListGruppos::route('/'),
            'create' => Pages\CreateGruppo::route('/create'),
            'edit' => Pages\EditGruppo::route('/{record}/edit'),
        ];
    }
}
