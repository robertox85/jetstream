<?php

namespace App\Filament\Resources;

use App\Filament\Exports\AssistitoExporter;
use App\Filament\Imports\AssistitoImporter;
use App\Filament\Resources\AssistitoResource\Pages;
use App\Filament\Resources\AssistitoResource\RelationManagers;


use App\Models\Anagrafica;
use App\Models\AnagraficaPratica;
use App\Models\Assistito;
use App\Models\Pratica;
use App\Models\Team;
use App\Traits\HasAnagraficaForm;
use App\Traits\HasPraticaForm;
use App\Traits\HasTeamAuthorizationScope;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Imports;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            ->schema(static::getAnagraficaFormSchema(true, 'assistito'));
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
                    ->sortable()
                ,

                Tables\Columns\TextColumn::make('pratica.numero_pratica')
                    ->label('Numero Pratica')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),


                Tables\Columns\TextColumn::make('nome_completo')
                    ->label('Nome/Denominazione')
                    ->searchable(['nome', 'cognome', 'denominazione'])
                    ->sortable(query: fn(Builder $query, string $direction) => $query->orderByNomeCompleto($direction)),


                TextColumn::make('tipo_utente')
                    ->label('Tipo Utente')
                    ->toggleable()
                    ->sortable()
                    ->toggledHiddenByDefault()
                ,

                TextColumn::make('denominazione')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ,

                // Dati personali
                TextColumn::make('nome')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                ,

                TextColumn::make('cognome')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
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
                    ->sortable()
                    ->limit(10)
                    ->wrap(),

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
                    ->modalHeading('Sei sicuro di voler eliminare questo assistito? Questa azione è irreversibile.')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Assistito eliminato')
                            ->body('Il record è stato eliminato con successo.') // Optional
                    ),
            ])
            ->headerActions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(AssistitoExporter::class),

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
