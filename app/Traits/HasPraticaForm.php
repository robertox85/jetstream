<?php

namespace App\Traits;

use App\Models\Team;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use function PHPUnit\Framework\containsIdentical;

trait HasPraticaForm
{
    use HasTeamUsers;
    protected static function getInformazioniPrincipaliSchema(): array
    {
        return [
            Forms\Components\Grid::make([
                'default' => 1,    // Una colonna su mobile
                'sm' => 1,         // Una colonna su schermi piccoli
                'md' => 2,         // Due colonne su tablet
                'lg' => 2,         // Due colonne su desktop
            ])
                ->schema([
                    Forms\Components\Section::make()
                        ->columns([
                            'default' => 1,
                            'sm' => 1,
                            'md' => 2,
                            'lg' => 2,
                        ])
                        ->schema([
                            Forms\Components\TextInput::make('numero_pratica')
                                ->label('Numero Pratica')
                                ->disabled()
                                ->dehydrated(false)
                                ->visible(fn($record) => $record !== null),
                            Forms\Components\TextInput::make('nome')
                                ->required(config('pratica-form.required_fields.nome'))
                                ->maxLength(255),
                            Forms\Components\Select::make('tipologia')
                                ->options(config('pratica-form.tipologie'))
                                ->required(config('pratica-form.required_fields.tipologia')),

                            Forms\Components\TextInput::make('competenza')
                                ->required(config('pratica-form.required_fields.competenza'))
                                ->maxLength(255),

                            Forms\Components\Select::make('team_id')
                                ->relationship('team', 'name', fn($query) => $query->where('personal_team', false))
                                ->required()
                                ->preload()
                                ->searchable()
                                ->label('Gruppo')
                                ->placeholder('Seleziona un gruppo')
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->label('Nome del gruppo')
                                        ->unique(Team::class, 'name'),
                                ])
                                ->createOptionUsing(function (array $data, Forms\Components\Select $component) {
                                    $team = Auth::user()->ownedTeams()->create([
                                        'name' => $data['name'],
                                        'personal_team' => false,
                                    ]);

                                    // Aggiorna le opzioni del select dopo la creazione
                                    $component->state($team->id);

                                    // Forza il refresh delle opzioni
                                    $component->options(
                                        Team::where('personal_team', false)
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    );

                                    return $team->id; // Ritorna l'ID invece dell'oggetto
                                })
                                ->afterStateUpdated(function ($state, Forms\Components\Select $component) {
                                    // Verifica che il valore sia valido
                                    if ($state && !Team::find($state)) {
                                        $component->state(null);
                                    }
                                })
                                ->loadingMessage('Caricamento gruppi...')
                                ->noSearchResultsMessage('Nessun gruppo trovato')
                                ->createOptionModalHeading('Crea nuovo gruppo')


                        ])
                    ,

                    Forms\Components\Section::make()
                        ->columns([
                            'default' => 1,
                            'sm' => 1,
                            'md' => 2,
                            'lg' => 2,
                        ])
                        ->schema([
                            Forms\Components\TextInput::make('ruolo_generale')
                                ->required(config('pratica-form.required_fields.ruolo_generale'))
                                ->maxLength(255),
                            Forms\Components\TextInput::make('giudice')
                                ->required(config('pratica-form.required_fields.giudice'))
                                ->maxLength(255),
                            Forms\Components\Select::make('stato')
                                ->required(config('pratica-form.required_fields.stato'))
                                ->options(config('pratica-form.stati'))
                                ->default('aperto'),

                        ])
                    ,

                    Forms\Components\Section::make('Note Aggiuntive')
                        ->columns([
                            'default' => 1,
                            'sm' => 1,
                            'md' => 2,
                            'lg' => 2,
                        ])
                        ->schema([
                            Forms\Components\Textarea::make('altri_riferimenti')
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ])
                    ,
                ])
        ];
    }

    protected static function getDocumentiSchema(): array
    {
        return [

            Forms\Components\RichEditor::make('descrizione')->nullable(),
            Forms\Components\FileUpload::make('file_path')
                ->label('File')
                ->visibility('private')
                ->preserveFilenames(true)
                ->storeFileNamesIn('original_name')
                ->disk(config('pratica-form.documenti.disk'))
                ->directory(config('pratica-form.documenti.directory'))
                ->maxSize((int)config('pratica-form.documenti.max_size'))
                ->downloadable()
                ->openable()
                ->openable()
                ->previewable()
                ->panelLayout('compact')
                ->columnSpan(1)
                ->getUploadedFileNameForStorageUsing(
                    function (TemporaryUploadedFile $file): string {
                        return $file->getClientOriginalName(); // Usa il nome originale del file
                    }
                )
        ];
    }

    protected static function getUdienzeSchema(): array
    {
        return [
            Forms\Components\Grid::make([
                'default' => 1,    // Una colonna su mobile
                'sm' => 1,         // Una colonna su schermi piccoli
                'md' => 2,         // Due colonne su tablet
                'lg' => 2,         // Due colonne su desktop
            ])
                ->schema([
                    Forms\Components\DateTimePicker::make('data_ora')
                        ->displayFormat('d/m/Y H:i')
                        ->live()
                        ->required(),
                    Forms\Components\TextInput::make('motivo')
                        ->required(),
                ]),
        ];
    }

    // scadenze
    protected static function getScadenzeSchema(): array
    {
        return [
            Forms\Components\Grid::make([
                'default' => 1,    // Una colonna su mobile
                'sm' => 1,         // Una colonna su schermi piccoli
                'md' => 2,         // Due colonne su tablet
                'lg' => 2,         // Due colonne su desktop
            ])
                ->schema([
                    Forms\Components\DateTimePicker::make('data_ora')
                        ->required()
                    ,
                    Forms\Components\TextInput::make('motivo')
                        ->required(),
                ]),
        ];
    }

    protected static function getNoteSchema(): array
    {
        //oggetto
        //nota
        //tipologia
        //visibilita
        return [
            Forms\Components\TextInput::make('oggetto')
                ->required(),
            Forms\Components\Textarea::make('nota')
                ->required(),
            Forms\Components\Select::make('tipologia')
                ->options(config('pratica-form.tipologie_note'))
                ->required(),
            Forms\Components\Select::make('visibilita')
                ->options(config('pratica-form.visibilita_note'))
                ->required(),
        ];
    }

    protected static function getLavorazioniSchema($include_owner = false): array
    {
        return [

            Forms\Components\Select::make('pratica_id')
                ->relationship('pratica', 'nome')
                ->required()
                ->preload()
                ->searchable()
                ->label('Pratica')
                ->placeholder('Seleziona una pratica')
                ->visible($include_owner),

            Forms\Components\Select::make('user_id')
                ->relationship('creator', 'name')
                ->required()
                ->preload()
                ->searchable()
                ->label('Creatore')
                ->placeholder('Seleziona un utente')
                ->options(static::getAllTeamUsers())
                ->visible($include_owner),

            Forms\Components\DateTimePicker::make('data_inizio'),
            Forms\Components\DateTimePicker::make('data_fine'),
            Forms\Components\Textarea::make('descrizione')
                ->required(),
        ];
    }

    protected static function getContabilitaSchema($include_owner = false): array
    {
        // data
        // descrizione
        // importo (decimale , 2 cifre, in euro)
        // tipo (entrata/uscita)
        return [

            Forms\Components\Select::make('pratica_id')
                ->relationship('pratica', 'nome')
                ->required()
                ->preload()
                ->searchable()
                ->label('Pratica')
                ->placeholder('Seleziona una pratica')
                ->visible($include_owner),

            Forms\Components\Select::make('user_id')
                ->relationship('creator', 'name')
                ->required()
                ->preload()
                ->searchable()
                ->label('Creatore')
                ->placeholder('Seleziona un utente')
                ->options(static::getAllTeamUsers())
                ->visible($include_owner),

            Forms\Components\Select::make('tipo')
                ->required()
                ->options(config('pratica-form.tipi_contabilita')),
            Forms\Components\Textarea::make('descrizione')
                ->required(),
            Forms\Components\TextInput::make('importo')
                ->type('number')
                ->step('0.01')
                ->validationAttribute('min', '0')
                ->prefix('€')
                ->placeholder('0.00'),
            Forms\Components\DateTimePicker::make('data'),
        ];
    }
}