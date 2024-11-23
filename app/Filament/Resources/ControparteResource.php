<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ControparteResource\Pages;
use App\Filament\Resources\ControparteResource\RelationManagers;
use App\Models\AnagraficaPratica;
use App\Models\Controparte;
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

class ControparteResource extends Resource
{
    use HasPraticaForm, HasAnagraficaForm;

    protected static ?string $model = Controparte::class;

    protected static ?string $modelLabel = 'Controparte';
    protected static ?string $pluralModelLabel = 'Controparti';

    protected static ?string $slug = 'controparti';
    protected static ?string $navigationGroup = 'Anagrafiche';



    use HasTeamAuthorizationScope;
    public static function getEloquentQuery(): Builder
    {
        return static::getAnagraficaBasedQuery();
    }




    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getAnagraficaFormSchema(true, 'controparte'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')  // Ordinamento singolo
            ->paginated([100, 150, 'all'])
            ->defaultPaginationPageOption(100)
            ->columns([

                Tables\Columns\TextColumn::make('pratica.nome')
                    ->label('Nome Pratica')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pratica.numero_pratica')
                    ->label('Numero Pratica')
                    ->getStateUsing(function ($record) {
                        // get from pivot table
                        $pratica = $record->pratiche->first();
                        if ($pratica) {
                            return $pratica->numero_pratica;
                        }
                        return null;
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('nome_completo')
                    ->label('Nome/Denominazione')
                    ->searchable(['nome', 'cognome', 'denominazione'])
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderByNomeCompleto($direction)),


                TextColumn::make('tipo_utente')
                    ->label('Tipo Utente')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable()
                ,

                TextColumn::make('denominazione')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable()
                ,

                // Dati personali
                TextColumn::make('nome')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->toggledHiddenByDefault()
                    ->sortable()
                ,

                TextColumn::make('cognome')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable()
                ,

                // Indirizzo
                TextColumn::make('indirizzo')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable()
                ,

                TextColumn::make('codice_postale')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable()
                ,

                TextColumn::make('citta')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable()
                ,

                TextColumn::make('provincia')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable()
                ,

                // Contatti
                TextColumn::make('telefono')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable()
                ,

                TextColumn::make('cellulare')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable()
                ,

                TextColumn::make('email')
                    ->searchable()
                    ->toggleable()
                ->sortable()

                ,

                TextColumn::make('pec')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable()
                ,

                // Dati fiscali
                TextColumn::make('codice_fiscale')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable()
                ,

                TextColumn::make('partita_iva')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable()
                ,

                TextColumn::make('codice_univoco_destinatario')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable()
                ,

                // Altri dati
                TextColumn::make('nota')
                    ->toggleable()
                    ->limit(10)
                    ->wrap()
                    ->sortable(),

                // Timestamp
                TextColumn::make('created_at')
                    ->label('Data Creazione')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Ultima Modifica')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable(),

                TextColumn::make('deleted_at')
                    ->label('Data Cancellazione')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Sei sicuro di voler eliminare questa controparte? Questa azione è irreversibile.')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Controparte eliminata')
                            ->body('La controparte è stata eliminata con successo.')
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
            'index' => Pages\ListContropartes::route('/'),
            'create' => Pages\CreateControparte::route('/create'),
            'edit' => Pages\EditControparte::route('/{record}/edit'),
        ];
    }
}
