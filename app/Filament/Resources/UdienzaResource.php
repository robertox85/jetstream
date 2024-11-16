<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UdienzaResource\Pages;
use App\Filament\Resources\UdienzaResource\RelationManagers;
use App\Models\Udienza;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UdienzaResource extends Resource
{
    protected static ?string $model = Udienza::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Udienza';
    protected static ?string $pluralModelLabel = 'Udienze';
    protected static ?string $slug = 'udienze';

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
            'index' => Pages\ListUdienzas::route('/'),
            'create' => Pages\CreateUdienza::route('/create'),
            'edit' => Pages\EditUdienza::route('/{record}/edit'),
        ];
    }
}
