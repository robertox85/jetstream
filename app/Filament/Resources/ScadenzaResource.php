<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScadenzaResource\Pages;
use App\Filament\Resources\ScadenzaResource\RelationManagers;
use App\Models\AnagraficaPratica;
use App\Models\Pratica;
use App\Models\Scadenza;
use App\Traits\HasTeamAuthorizationScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class ScadenzaResource extends Resource
{
    protected static ?string $model = Scadenza::class;

    //  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Scadenza';
    protected static ?string $pluralModelLabel = 'Scadenze';

    protected static ?string $slug = 'scadenze';
    protected static ?string $navigationGroup = 'Agenda';

    use HasTeamAuthorizationScope;

    public static function getEloquentQuery(): Builder
    {
        return static::getPraticaBasedQuery();
    }

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
                // data_ora
                // motivo
                // stato
                // pratica_id

                Tables\Columns\TextColumn::make('data_ora')
                    ->label('Data e Ora')
                    ->searchable(),
                Tables\Columns\TextColumn::make('motivo')
                    ->label('Motivo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pratica_id')
                    ->label('Pratica')
                    ->searchable()
                ->getStateUsing(fn ($record) => $record->pratica->numero_pratica ?? 'N/A'),

                Tables\Columns\TextColumn::make('stato')
                    ->label('Stato')
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
            'index' => Pages\ListScadenzas::route('/'),
            'create' => Pages\CreateScadenza::route('/create'),
            'edit' => Pages\EditScadenza::route('/{record}/edit'),
        ];
    }
}
