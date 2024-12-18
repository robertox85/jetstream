<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppuntamentoResource\Pages;
use App\Filament\Resources\AppuntamentoResource\RelationManagers;
use App\Models\Evento;
use App\Traits\HasEventoForm;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppuntamentoResource extends Resource
{
    use HasEventoForm;

    protected static ?string $model = Evento::class;

    protected static ?string $label = 'Appuntamento';
    protected static ?string $pluralLabel = 'Appuntamenti';

    protected static ?string $slug = 'appuntamenti';


    protected static ?string $navigationGroup = 'Agenda';
    protected static ?int $navigationSort = 2;


    // get only appuntamenti
    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->where('tipo', 'appuntamento');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getEventoForm('appuntamento'));

    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns('appuntamento'))
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Sei sicuro di voler eliminare questo appuntamento? Questa azione è irreversibile.')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Appuntamento eliminato')
                            ->body('L\'appuntamento è stato eliminato con successo.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')  // Ordinamento singolo
            ->paginated([100, 150, 'all'])
            ->defaultPaginationPageOption(100);
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
            'index' => Pages\ListAppuntamentos::route('/'),
            'create' => Pages\CreateAppuntamento::route('/create'),
            'edit' => Pages\EditAppuntamento::route('/{record}/edit'),
        ];
    }
}
