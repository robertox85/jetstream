<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotaResource\Pages;
use App\Filament\Resources\NotaResource\RelationManagers;
use App\Models\Nota;
use App\Models\Pratica;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotaResource extends Resource
{
    protected static ?string $model = Nota::class;

    //  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Nota';
    protected static ?string $pluralModelLabel = 'Note';

    protected static ?string $slug = 'note';
    protected static ?string $navigationGroup = 'Gestione Pratiche';


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
                            ->options([
                                'Nota' => 'Nota',
                                'Promemoria' => 'Promemoria',
                                'Scadenza' => 'Scadenza',
                                'Richiesta' => 'Richiesta',
                                'Altro' => 'Altro',
                            ])
                            ->required(),
                        Forms\Components\Select::make('visibilita')
                            ->label('Visibilità')
                            ->options([
                                'Pubblica' => 'Pubblica',
                                'Privata' => 'Privata',
                            ])
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('oggetto')
                    ->limit(30)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipologia')
                    ->searchable()
                    ->sortable(),


                Tables\Columns\TextColumn::make('pratica_id')
                    ->label('Pratica')
                    ->getStateUsing(function ($record) {
                        $pratica_id = $record->pratica_id;
                        return Pratica::find($pratica_id)->numero_pratica ?? 'N/A';
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('visibilita')
                    ->badge()
                    ->color(fn ($record) => match ($record->visibilita) {
                        'pubblica' => 'success',
                        'privata' => 'danger',
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
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