<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotaResource\Pages;
use App\Filament\Resources\NotaResource\RelationManagers;
use App\Models\Nota;
use App\Models\Pratica;
use App\Models\User;
use App\Policies\Traits\HasTeamScope;
use App\Traits\HasTeamAuthorizationScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class NotaResource extends Resource
{


    protected static ?string $model = Nota::class;

    //  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Nota';
    protected static ?string $pluralModelLabel = 'Note';

    protected static ?string $slug = 'note';
    protected static ?string $navigationGroup = 'Gestione Pratiche';

    protected static ?int $navigationSort = 3;

    use HasTeamAuthorizationScope;

    public static function getEloquentQuery(): Builder
    {
       $query = static::getPraticaBasedQuery();
       return static::addVisibilityScope($query, true);

    }

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('pratica_id')
                            ->label('Pratica')
                            ->options(
                                Pratica::all()->pluck('numero_pratica', 'id')
                            )
                            ->required()
                            ->live()
                            ->searchable(),
                        Forms\Components\Select::make('user_id')
                            ->label('Utente')
                            ->options(
                                User::all()->pluck('name', 'id')
                            )
                            ->required()
                            ->live()
                            ->searchable(),
                        Forms\Components\TextInput::make('oggetto')
                            ->label('Oggetto')
                            ->required(),
                        Forms\Components\Textarea::make('nota')
                            ->label('Nota')
                            ->required(),
                        Forms\Components\Select::make('tipologia')
                            ->label('Tipologia')
                            ->options(config('pratica-form.tipologie_note'))
                            ->required(),
                        Forms\Components\Select::make('visibilita')
                            ->label('Visibilità')
                            ->options(config('pratica-form.visibilita_note'))
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')  // Ordinamento singolo
            ->paginated([100, 150, 'all'])
            ->defaultPaginationPageOption(100)
            ->columns([

                // Nome pratica
                Tables\Columns\TextColumn::make('pratica.nome')
                    ->label('Pratica')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('oggetto')
                    ->limit(30)
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipologia')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),


                Tables\Columns\TextColumn::make('pratica_id')
                    ->label('Nr. Pratica')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->getStateUsing(function ($record) {
                        $pratica_id = $record->pratica_id;
                        return Pratica::find($pratica_id)->numero_pratica ?? 'N/A';
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('visibilita')
                    ->badge()
                    ->toggleable()
                    ->color(fn($record) => match ($record->visibilita) {
                        'pubblica' => 'success',
                        'privata' => 'danger',
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('pratica_id')
                    ->label('Pratica')
                    ->options(
                        Pratica::all()->pluck('numero_pratica', 'id')
                    )
                    ->searchable(),
                Tables\Filters\SelectFilter::make('tipologia')
                    ->label('Tipologia')
                    ->options(config('pratica-form.tipologie_note'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('visibilita')
                    ->label('Visibilità')
                    ->options(config('pratica-form.visibilita_note'))
                    ->searchable(),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListNotas::route('/'),
            'create' => Pages\CreateNota::route('/create'),
            'edit' => Pages\EditNota::route('/{record}/edit'),
        ];
    }
}
