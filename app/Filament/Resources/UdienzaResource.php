<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UdienzaResource\Pages;
use App\Filament\Resources\UdienzaResource\RelationManagers;
use App\Models\Evento;
use App\Traits\HasEventoForm;
use App\Traits\HasTeamAuthorizationScope;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UdienzaResource extends Resource
{
    use HasEventoForm;

    protected static ?string $model = Evento::class;

    //  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Udienza';
    protected static ?string $pluralModelLabel = 'Udienze';
    protected static ?string $slug = 'udienze';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 2;

    use HasTeamAuthorizationScope;

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->where('tipo', 'udienza');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getEventoForm( 'udienza' ));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns( 'udienza' ))
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Sei sicuro di voler eliminare questa udienza? Questa azione è irreversibile.')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Udienza eliminata')
                            ->body('L\'udienza è stata eliminata con successo.')
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
            'index' => Pages\ListUdienzas::route('/'),
            'create' => Pages\CreateUdienza::route('/create'),
            'edit' => Pages\EditUdienza::route('/{record}/edit'),
        ];
    }
}
