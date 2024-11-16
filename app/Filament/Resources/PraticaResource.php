<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PraticaResource\Pages;
use App\Filament\Resources\PraticaResource\RelationManagers;
use App\Models\Pratica;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PraticaResource extends Resource
{
    protected static ?string $model = Pratica::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Pratica';
    protected static ?string $pluralModelLabel = 'Pratiche';
    protected static ?string $slug = 'pratiche';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListPraticas::route('/'),
            'create' => Pages\CreatePratica::route('/create'),
            'edit' => Pages\EditPratica::route('/{record}/edit'),
        ];
    }
}
