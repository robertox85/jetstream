<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UdienzaResource\Pages;
use App\Filament\Resources\UdienzaResource\RelationManagers;
use App\Models\Pratica;
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

    //  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Udienza';
    protected static ?string $pluralModelLabel = 'Udienze';
    protected static ?string $slug = 'udienze';

    protected static ?string $navigationGroup = 'Agenda';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('data_ora')
                            ->label('Data e Ora')
                            ->required(),
                        Forms\Components\TextInput::make('motivo')
                            ->label('Motivo')
                            ->required(),
                        Forms\Components\TextInput::make('luogo')
                            ->label('Luogo')
                            ->required(),
                        Forms\Components\Select::make('pratica_id')
                            ->label('Pratica')
                            ->options(
                                Pratica::all()->pluck('numero_pratica', 'id')
                            )
                            ->required()
                            ->live()
                            ->searchable(),
                        Forms\Components\Select::make('stato')
                            ->label('Stato')
                            ->options([
                                'in_corso' => 'In corso',
                                'da_iniziare' => 'Da iniziare',
                                'completata' => 'Completata',
                                'annullata' => 'Annullata',
                            ])
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('data_ora')
                    ->label('Data e Ora')
                    ->searchable()
                    ->date('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('motivo')
                    ->limit(20)
                    ->searchable(),

                Tables\Columns\TextColumn::make('luogo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pratica_id')
                    ->label('Pratica')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->pratica->numero_pratica ?? 'N/A'),

                Tables\Columns\TextColumn::make('stato')
                    ->label('Stato')
                    ->searchable()
                    ->badge()
                    ->color(fn($record) => match ($record->stato) {
                        'in_corso' => 'info',
                        'da_iniziare' => 'warning',
                        'completata' => 'success',
                        'annullata' => 'danger',
                    }),
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
