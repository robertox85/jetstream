<?php

namespace App\Traits;

use App\Filament\Resources\PraticaResource;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Support\Facades\Log;

trait HasEventoForm
{

    public static function getEventoForm($tipo = 'scadenza', ?array $defaultData = null)
    {
        $defaultData = $defaultData ?? [];

        $isPraticaResource = static::class === PraticaResource::class;

        return [

            Forms\Components\Grid::make(2)
                ->schema([
                    // HIdden field to store the type of the event
                    Forms\Components\Select::make('pratica_id')
                        ->label('Pratica')
                        ->searchable()
                        ->required($tipo !== 'appuntamento')
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


                    // Nel form
                    Forms\Components\Select::make('invitati')
                        ->multiple()
                        ->label('Invitati')
                        ->relationship('invitati', 'name')
                        ->preload()
                        ->searchable()
                        ,

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

                    Forms\Components\DatePicker::make('data')
                        ->label('Data')
                        ->required()
                        ->afterStateHydrated(function ($state, callable $set, callable $get) {
                            $data_ora = $get('data_ora') ?? null;
                            if ($data_ora) {
                                $set('data', Carbon::parse($data_ora)->format('Y-m-d'));
                            }
                        })
                        ->default($defaultData['data'] ?? Carbon::now()->format('Y-m-d'))
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $data = $state ?? null;
                            $ora = $get('ora') ?? null;
                            if ($data && $ora) {
                                $set('data_ora', Carbon::parse($data . ' ' . $ora)->format('Y-m-d H:i:s'));
                            }
                        }),

                    Forms\Components\TimePicker::make('ora')
                        ->label('Orario')
                        ->required()
                        ->afterStateHydrated(function ($state, callable $set, callable $get) {
                            $data_ora = $get('data_ora') ?? null;
                            if ($data_ora) {
                                $set('ora', Carbon::parse($data_ora)->format('H:i:s'));
                            }
                        })
                        ->default($defaultOra['ora'] ?? Carbon::now()->format('H:i:s'))
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $data = $get('data') ?? null;
                            $ora = $state ?? null;

                            if ($data && $ora) {
                                $set('data_ora', Carbon::parse($data . ' ' . $ora)->format('Y-m-d H:i:s'));
                            }
                        }),

                    Forms\Components\Hidden::make('data_ora')
                        ->default($defaultData['data_ora'] ?? Carbon::now()->format('Y-m-d H:i:s')),


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

            Tables\Columns\TextColumn::make('invitati')
                ->label('Invitati')
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