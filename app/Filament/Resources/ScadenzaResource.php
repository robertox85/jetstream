<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScadenzaResource\Pages;
use App\Filament\Resources\ScadenzaResource\RelationManagers;
use App\Models\AnagraficaPratica;
use App\Models\Pratica;
use App\Models\Scadenza;
use App\Traits\HasEventoForm;
use App\Traits\HasTeamAuthorizationScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class ScadenzaResource extends Resource
{
    use HasEventoForm;

    protected static ?string $model = Scadenza::class;

    //  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Scadenza';
    protected static ?string $pluralModelLabel = 'Scadenze';

    protected static ?string $slug = 'scadenze';
    protected static ?string $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 3;

    use HasTeamAuthorizationScope;

    public static function getEloquentQuery(): Builder
    {
        return static::getPraticaBasedQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getEventoForm( 'scadenza' ));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns( 'scadenza' ))
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
            'index' => Pages\ListScadenzas::route('/'),
            'create' => Pages\CreateScadenza::route('/create'),
            'edit' => Pages\EditScadenza::route('/{record}/edit'),
        ];
    }
}
