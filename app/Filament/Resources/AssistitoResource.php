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
            ->schema(static::getAnagraficaFormSchema(false));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome_completo')
                    ->label('Nome/Denominazione')
                    ->searchable(['nome', 'cognome', 'denominazione'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'primary' => 'controparte',
                        'success' => 'assistito',
                    ]),
                Tables\Columns\TextColumn::make('pratiche.nome')
                    ->label('Pratiche')
                    ->searchable()
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
