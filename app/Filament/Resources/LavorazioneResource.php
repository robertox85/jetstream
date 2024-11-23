<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LavorazioneResource\Pages;
use App\Filament\Resources\LavorazioneResource\RelationManagers;
use App\Models\Lavorazione;
use App\Traits\HasPraticaForm;
use App\Traits\HasTeamAuthorizationScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LavorazioneResource extends Resource
{
    protected static ?string $model = Lavorazione::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Lavorazione';
    protected static ?string $pluralModelLabel = 'Lavorazioni';

    protected static ?string $slug = 'lavorazioni';
    protected static ?string $navigationGroup = 'Gestione Pratiche';
    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    use HasTeamAuthorizationScope;
    use HasPraticaForm;

    public static function getEloquentQuery(): Builder
    {
        $query = static::getPraticaBasedQuery();
        return static::addVisibilityScope($query);
    }

    public static function form(Form $form): Form
    {
        // pratica
        // owner
        // descrizione
        // data inizio
        // data fine
        return $form
            ->schema(static::getLavorazioniSchema(true));
    }

    public static function table(Table $table): Table
    {
        //  pratica
        //  owner
        // descrizione (limitata a 50 caratteri)
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
                    ->label('Nr. Pratica')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creatore')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descrizione')
                    ->label('Descrizione')
                    ->searchable()
                    ->limit(10)
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
            'index' => Pages\ListLavoraziones::route('/'),
            'create' => Pages\CreateLavorazione::route('/create'),
            'edit' => Pages\EditLavorazione::route('/{record}/edit'),
        ];
    }
}
