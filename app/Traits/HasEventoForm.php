<?php

namespace App\Traits;

use App\Filament\Resources\PraticaResource;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

trait HasEventoForm
{

    public static function getEventoForm($tipo = 'scadenza', ?array $defaultData = null)
    {
        /**
         * pratica_id
         * user_id
         * assigned_to
         * tipo
         * data_ora
         * motivo
         * luogo
         * stato
         * reminder_at
         * email_notification
         * browser_notification
         */

        $isPraticaResource = static::class === PraticaResource::class;

        return [

            Forms\Components\Grid::make(2)
                ->schema([
                    // HIdden field to store the type of the event
                    Forms\Components\Select::make('pratica_id')
                        ->label('Pratica')
                        ->searchable()
                        ->required()
                        ->preload()
                        ->relationship('pratica', 'nome')
                        ->hidden(fn() => $isPraticaResource),

                    Forms\Components\Select::make('user_id')
                        ->label('Creato da')
                        ->searchable()
                        ->preload()
                        ->relationship('user', 'name')
                        ->hidden(fn() => $isPraticaResource)
                        ->default(auth()->id()),


                    Forms\Components\Select::make('assigned_to')
                        ->label('Assegnato a')
                        ->searchable()
                        ->preload()
                        ->default(auth()->id())
                        ->relationship('user', 'name'),

                    Forms\Components\Hidden::make('tipo')
                        ->default($tipo)
                        ->hidden(fn() => !$isPraticaResource),

                    Forms\Components\Select::make('tipo')
                        ->label('Tipo')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->default($tipo)
                        ->options([
                            'scadenza' => 'Scadenza',
                            'udienza' => 'Udienza',
                            'appuntamento' => 'Appuntamento',
                        ])
                        ->hidden(fn() => $isPraticaResource),


                    Forms\Components\Select::make('stato')
                        ->label('Stato')
                        ->searchable()
                        ->preload()
                        ->default('da_iniziare')
                        ->options([
                            'da_iniziare' => 'Da iniziare',
                            'in_corso' => 'In corso',
                            'completata' => 'Completato',
                            'annullata' => 'Annullato',
                        ]),

                    // Fill the data_ora field with the concatenation of the data and ora fields
                    Forms\Components\DatePicker::make('data')
                        ->label('Data')
                        ->required()
                        ->afterStateHydrated(function ($state, callable $set, callable $get) {
                            if ($dataOra = $get('data_ora')) {
                                $set('data', Carbon::parse($dataOra)->format('Y-m-d'));
                            }
                        })
                        ->default($defaultData['data'] ?? Carbon::now()->format('Y-m-d')),

                    Forms\Components\TimePicker::make('ora')
                        ->label('Orario')
                        ->required()
                        ->afterStateHydrated(function ($state, callable $set, callable $get) {
                            if ($dataOra = $get('data_ora')) {
                                $set('ora',
                                    Carbon::parse($dataOra, 'Europe/Rome')->format('H:i')
                                );
                            }
                        })
                        ->default($defaultOra['ora'] ?? Carbon::now()->format('H:i')),


                    Forms\Components\TextInput::make('luogo')
                        ->label('Luogo')
                        ->live(),

                    Forms\Components\Textarea::make('motivo')
                        ->label('Motivo')
                        ->required()
                        ->default('')
                        ->rows(5)
                        ->columnSpanFull(),
                ])
        ];
    }


    public static function getTableColumns($tipo = 'evento')
    {
        return [
            Tables\Columns\TextColumn::make('pratica.nome')
                ->label('Pratica'),
            Tables\Columns\TextColumn::make('user.name')
                ->label('Creato da'),
            Tables\Columns\TextColumn::make('assigned_to')
                ->label('Assegnato a')
                ->toggleable()
                ->toggledHiddenByDefault(true),

            Tables\Columns\TextColumn::make('tipo')
                ->label('Tipo')
                ->toggleable()
                ->toggledHiddenByDefault(true),

            Tables\Columns\TextColumn::make('motivo')
                ->label('Motivo')
                ->toggleable()
                ->toggledHiddenByDefault(true),
            Tables\Columns\TextColumn::make('luogo')
                ->label('Luogo')
                ->hidden(fn() => $tipo === 'scadenza'),
            Tables\Columns\TextColumn::make('data_ora')
                ->label('Data e ora'),
            Tables\Columns\TextColumn::make('stato')
                ->label('Stato')
                ->toggleable()
                ->toggledHiddenByDefault(true),
        ];
    }


}