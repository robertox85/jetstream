<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContabilitaResource\Pages;
use App\Filament\Resources\ContabilitaResource\RelationManagers;
use App\Models\Contabilita;
use App\Traits\HasPraticaForm;
use App\Traits\HasTeamAuthorizationScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContabilitaResource extends Resource
{
    protected static ?string $model = Contabilita::class;

    protected static ?string $modelLabel = 'Contabilità';
    protected static ?string $pluralModelLabel = 'Contabilità';

    protected static ?string $slug = 'contabilita';
    protected static ?string $navigationGroup = 'Gestione Pratiche';

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
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
        return $form
            ->schema(static::getContabilitaSchema(true));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pratica.nome')
                    ->label('Nome Pratica')
                    ->searchable(),
                Tables\Columns\TextColumn::make('descrizione')
                    ->searchable(),
                Tables\Columns\TextColumn::make('importo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (Contabilita $record) => $record->tipo === 'entrata' ? 'success' : 'danger')
                    ->searchable(),
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
            'index' => Pages\ListContabilitas::route('/'),
            'create' => Pages\CreateContabilita::route('/create'),
            'edit' => Pages\EditContabilita::route('/{record}/edit'),
        ];
    }
}
