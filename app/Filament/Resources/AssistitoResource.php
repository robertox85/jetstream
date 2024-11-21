<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssistitoResource\Pages;
use App\Filament\Resources\AssistitoResource\RelationManagers;

use App\Models\Anagrafica;
use App\Models\AnagraficaPratica;
use App\Models\Assistito;
use App\Models\Pratica;
use App\Traits\HasAnagraficaForm;
use App\Traits\HasPraticaForm;
use App\Traits\HasTeamAuthorizationScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssistitoResource extends Resource
{
    protected static ?string $model = Assistito::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Assistito';
    protected static ?string $pluralModelLabel = 'Assistiti';

    protected static ?string $slug = 'assistiti';
    protected static ?string $navigationGroup = 'Anagrafiche';

    use HasPraticaForm, HasAnagraficaForm, HasTeamAuthorizationScope;


    public static function getEloquentQuery(): Builder
    {
        return static::getAnagraficaBasedQuery();
    }

    public static function form(Form $form): Form
    {

        return $form
            ->schema(static::getAnagraficaFormSchema(true,'assistito'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('pratica.nome')
                    ->label('Nome Pratica')
                    ->getStateUsing(function ($record) {
                        // get from pivot table
                        $pratica = $record->pratiche->first();
                        if ($pratica) {
                            return $pratica->nome;
                        }
                        return null;
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nome_completo')
                    ->label('Nome/Denominazione')
                    ->searchable(['nome', 'cognome', 'denominazione'])
                    ->sortable(),


                TextColumn::make('tipo_utente')
                    ->label('Tipo Utente')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ,

                TextColumn::make('denominazione')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->toggledHiddenByDefault()
                ,

                // Dati personali
                TextColumn::make('nome')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->toggledHiddenByDefault()
                ,

                TextColumn::make('cognome')
                    ->searchable()
                    ->toggledHiddenByDefault()
                    ->sortable()
                    ->toggleable()
                ,

                // Indirizzo
                TextColumn::make('indirizzo')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ,

                TextColumn::make('codice_postale')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ,

                TextColumn::make('citta')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ,

                TextColumn::make('provincia')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ,

                // Contatti
                TextColumn::make('telefono')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ,

                TextColumn::make('cellulare')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ,

                TextColumn::make('email')
                    ->searchable()
                    ->toggledHiddenByDefault()
                    ->toggleable()
                ,

                TextColumn::make('pec')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ,

                // Dati fiscali
                TextColumn::make('codice_fiscale')
                    ->searchable()
                    ->toggledHiddenByDefault()
                    ->toggleable()
                ,

                TextColumn::make('partita_iva')
                    ->searchable()
                    ->toggledHiddenByDefault()
                    ->toggleable()
                ,

                TextColumn::make('codice_univoco_destinatario')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ,

                // Altri dati
                TextColumn::make('nota')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->limit(10)
                    ->wrap(),

                // Timestamp
                TextColumn::make('created_at')
                    ->label('Data Creazione')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('updated_at')
                    ->label('Ultima Modifica')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('deleted_at')
                    ->label('Data Cancellazione')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Sei sicuro di voler eliminare questo assistito? Questa azione è irreversibile.')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Assistito eliminato')
                            ->body('Il record è stato eliminato con successo.') // Optional
                    ),
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
            'index' => Pages\ListAssistitos::route('/'),
            'create' => Pages\CreateAssistito::route('/create'),
            'edit' => Pages\EditAssistito::route('/{record}/edit'),
        ];
    }
}
