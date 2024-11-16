<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaDocumentoResource\Pages;
use App\Filament\Resources\CategoriaDocumentoResource\RelationManagers;
use App\Models\CategoriaDocumento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoriaDocumentoResource extends Resource
{
    protected static ?string $model = CategoriaDocumento::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Categoria Documento';
    protected static ?string $pluralModelLabel = 'Categorie Documenti';
    protected static ?string $slug = 'categorie_documenti';

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
            'index' => Pages\ListCategoriaDocumentos::route('/'),
            'create' => Pages\CreateCategoriaDocumento::route('/create'),
            'edit' => Pages\EditCategoriaDocumento::route('/{record}/edit'),
        ];
    }
}
