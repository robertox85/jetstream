<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnagraficaResource\Pages;
use App\Filament\Resources\AnagraficaResource\RelationManagers;
use App\Models\Anagrafica;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnagraficaResource extends Resource
{
    protected static ?string $model = Anagrafica::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Anagrafica';
    protected static ?string $pluralModelLabel = 'Anagrafiche';

    protected static ?string $slug = 'anagrafiche';
    protected static ?string $navigationGroup = 'Anagrafiche';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {

        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome_completo')
                    ->label('Nome/Denominazione')
                    ->searchable(['nome', 'cognome', 'denominazione'])
                    ->sortable(),




                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),


                Tables\Columns\TextColumn::make('pratiche.nome')
                    ->label('Pratiche')
                    ->searchable()


                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable()
                    ->colors([
                        'primary' => 'controparte',
                        'success' => 'assistito',
                    ]),
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
            'index' => Pages\ListAnagraficas::route('/'),
            'create' => Pages\CreateAnagrafica::route('/create'),
            'edit' => Pages\EditAnagrafica::route('/{record}/edit'),
        ];
    }
}