<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentoResource\Pages;
use App\Filament\Resources\DocumentoResource\RelationManagers;
use App\Models\Documento;
use App\Traits\HasPraticaForm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class DocumentoResource extends Resource
{
    use HasPraticaForm;

    protected static ?string $model = Documento::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Documento';
    protected static ?string $pluralModelLabel = 'Documenti';
    protected static ?string $slug = 'documenti';

    protected static ?string $navigationGroup = 'Documenti';

    protected static bool $shouldRegisterNavigation = false;



    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getDocumentiSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')  // Ordinamento singolo
            ->paginated([100, 150, 'all'])
            ->defaultPaginationPageOption(100)
            ->columns([
                // categoria_id
                // file_path
                ///descrizione
                // pratica_id

                Tables\Columns\TextColumn::make('categoria.nome')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->url(fn (Documento $documento) => $documento->file_path)
                    ->openUrlInNewTab()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('descrizione')
                    ->label('Descrizione')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),

                Tables\Columns\TextColumn::make('pratica.numero_pratica')
                    ->label('Pratica')
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
            'index' => Pages\ListDocumentos::route('/'),
            'create' => Pages\CreateDocumento::route('/create'),
            'edit' => Pages\EditDocumento::route('/{record}/edit'),
        ];
    }
}
