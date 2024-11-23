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

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Categoria Documento';
    protected static ?string $pluralModelLabel = 'Categorie Documenti';
    protected static ?string $slug = 'categorie_documenti';

    protected static ?string $navigationGroup = 'Documenti';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Nome')
                    ->required(),
                Forms\Components\Textarea::make('descrizione')
                    ->label('Descrizione')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')  // Ordinamento singolo
            ->paginated([100, 150, 'all'])
            ->defaultPaginationPageOption(100)
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descrizione')
                    ->label('Descrizione')
                    ->searchable()
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
            'index' => Pages\ListCategoriaDocumentos::route('/'),
            'create' => Pages\CreateCategoriaDocumento::route('/create'),
            'edit' => Pages\EditCategoriaDocumento::route('/{record}/edit'),
        ];
    }
}
