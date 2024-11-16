<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScadenzaResource\Pages;
use App\Filament\Resources\ScadenzaResource\RelationManagers;
use App\Models\Scadenza;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScadenzaResource extends Resource
{
    protected static ?string $model = Scadenza::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Scadenza';
    protected static ?string $pluralModelLabel = 'Scadenze';
    protected static ?string $slug = 'scadenze';

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
            'index' => Pages\ListScadenzas::route('/'),
            'create' => Pages\CreateScadenza::route('/create'),
            'edit' => Pages\EditScadenza::route('/{record}/edit'),
        ];
    }
}
