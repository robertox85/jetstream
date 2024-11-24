<?php

namespace App\Traits;

use App\Models\Anagrafica;
use Filament\Forms;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait HasAnagraficaForm
{
    public static function getAnagraficaFormSchema(bool $includeType = false, $type = "assistito"): array
    {
        $schema = [];

        if ($includeType) {
            $schema[] = Forms\Components\Grid::make(2)
                ->schema([
                    //Forms\Components\Select::make('type')
                    //    ->label('Tipologia Anagrafica')
                    //    ->options([
                    //        Anagrafica::TYPE_CONTROPARTE => 'Controparte',
                    //        Anagrafica::TYPE_ASSISTITO => 'Assistito',
                    //    ])
                    //    ->required()
                    //    ->default($type)
                    //    ->native(false),

                    // if i'm in 'Controrparte' view, i want to show the 'pratica_id' field
                    Forms\Components\Select::make('pratica_id')
                        ->label('Pratica')
                        ->relationship('pratiche', 'numero_pratica')
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('tipo_utente')
                        ->label('Tipo')
                        ->options([
                            Anagrafica::TIPO_PERSONA => 'Persona',
                            Anagrafica::TIPO_AZIENDA => 'Azienda',
                        ])
                        ->required()
                        ->live()
                        ->native(false),
                ]);
        } else {
            $schema[] = Forms\Components\Select::make('tipo_utente')
                ->label('Tipo')
                ->options([
                    Anagrafica::TIPO_PERSONA => 'Persona',
                    Anagrafica::TIPO_AZIENDA => 'Azienda',
                ])
                ->required()
                ->live()
                ->native(false)
                ->columnSpanFull();
        }

        $baseSchema = [

            Forms\Components\TextInput::make('denominazione')
                ->label('Denominazione')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->visible(fn($get) => $get('tipo_utente') === Anagrafica::TIPO_AZIENDA)
                ->columnSpanFull(),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('nome')
                        ->required()
                        ->visible(fn($get) => $get('tipo_utente') === Anagrafica::TIPO_PERSONA)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('cognome')
                        ->required()
                        ->visible(fn($get) => $get('tipo_utente') === Anagrafica::TIPO_PERSONA)
                        ->maxLength(255),

            ]),


            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('codice_fiscale')
                        ->label('Codice Fiscale')
                        // ->required(fn($get) => $get('tipo_utente') === Anagrafica::TIPO_PERSONA)
                        ->maxLength(16)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('partita_iva')
                        ->label('Partita IVA')
                        // ->required(fn($get) => $get('tipo_utente') === Anagrafica::TIPO_AZIENDA)
                        ->maxLength(11)
                        ->unique(ignoreRecord: true),
                ]),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('email')
                        ->email()
                        // ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('pec')
                        ->label('PEC')
                        ->email()
                        // ->required(fn($get) => $get('tipo_utente') === Anagrafica::TIPO_AZIENDA)
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                ]),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('telefono')
                        ->tel()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('cellulare')
                        ->tel()
                        ->maxLength(255),
                ]),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('indirizzo')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('codice_postale')
                        ->label('CAP')
                        ->maxLength(5),
                ]),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('citta')
                        ->label('Città')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('provincia')
                        ->maxLength(2),
                ]),

            Forms\Components\TextInput::make('codice_univoco_destinatario')
                ->label('Codice Univoco')
                ->maxLength(7),

            Forms\Components\Textarea::make('nota')
                ->maxLength(65535)
                ->columnSpanFull(),
        ];

        return array_merge($schema, $baseSchema);
    }

    public static function getCreateOptionFormSchema(string $type): array
    {
        return [
            Forms\Components\Select::make('tipo_utente')
                ->options([
                    Anagrafica::TIPO_PERSONA => 'Persona',
                    Anagrafica::TIPO_AZIENDA => 'Azienda',
                ])
                ->required()
                ->live()
                ->afterStateUpdated(fn($state, Forms\Set $set) => $set('type', $type)),

            Forms\Components\Hidden::make('type')
                ->default($type),

            Forms\Components\TextInput::make('denominazione')
                ->required()
                ->visible(fn($get) => $get('tipo_utente') === Anagrafica::TIPO_AZIENDA),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('nome')
                        ->required()
                        ->visible(fn($get) => $get('tipo_utente') === Anagrafica::TIPO_PERSONA),

                    Forms\Components\TextInput::make('cognome')
                        ->required()
                        ->visible(fn($get) => $get('tipo_utente') === Anagrafica::TIPO_PERSONA),
                ]),

            Forms\Components\TextInput::make('email')
                ->email()
                // ->required()
                ->unique(ignoreRecord: true)
                ->live(debounce: 500)
                ->dehydrated(true)
                ->validationMessages([
                    'unique' => 'L\'indirizzo email è già in uso.',
                    'email' => 'Inserisci un indirizzo email valido.',
                ])
                ->suffixIcon(fn($state, $record) => $state && filter_var($state, FILTER_VALIDATE_EMAIL)
                    ? 'heroicon-o-check-circle'
                    : 'heroicon-o-x-circle'
                )
                ->suffixIconColor(fn($state, $record) => $state && filter_var($state, FILTER_VALIDATE_EMAIL)
                    ? 'success'
                    : 'danger'
                )
                ->afterStateUpdated(function (Get $get, $state, Forms\Components\TextInput $component) {
                    $component->hint(fn($state) => $state
                        ? 'Premere invio per confermare'
                        : 'Inserisci un indirizzo email valido'
                    );
                })
                ->hint(fn($state) => $state
                    ? 'Premere invio per confermare'
                    : 'Inserisci un indirizzo email valido'
                )
        ];
    }
}