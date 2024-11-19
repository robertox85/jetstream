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
