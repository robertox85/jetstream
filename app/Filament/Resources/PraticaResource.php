<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PraticaResource\Pages;



use App\Models\Anagrafica;

use App\Models\Assistito;
use App\Models\Controparte;
use App\Models\Pratica;
use App\Models\Team;
use App\Traits\HasAnagraficaForm;
use App\Traits\HasPraticaForm;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PraticaResource extends Resource
{

    use HasPraticaForm, HasAnagraficaForm;

    protected static ?string $model = Pratica::class;

    //  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Pratica';
    protected static ?string $pluralModelLabel = 'Pratiche';
    protected static ?string $slug = 'pratiche';

    protected static ?string $navigationGroup = 'Gestione Pratiche';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Tabs::make('Pratica')
                ->tabs([
                    // Informazioni Principali
                    Forms\Components\Tabs\Tab::make('Informazioni Principali')
                        ->icon('heroicon-m-information-circle')
                        ->schema(static::getInformazioniPrincipaliSchema()),

                    // Tab Scadenze
                    Forms\Components\Tabs\Tab::make('Scadenze')
                        ->icon('heroicon-m-calendar')
                        ->schema([
                            Forms\Components\Section::make('Gestione Scadenze')
                                ->description('Gestisci le scadenze della pratica')
                                ->schema([
                                    Forms\Components\Repeater::make('scadenze')
                                        ->columnSpanFull()
                                        ->collapsible()
                                        ->collapsed()
                                        ->itemLabel(fn(array $state): ?string => Carbon::parse($state['data_ora'])->format('d/m/Y H:i') . ' - ' . $state['motivo'])
                                        ->relationship('scadenze')
                                        ->defaultItems(0)
                                        ->schema(static::getScadenzeSchema()),
                                ]),
                        ])
                        ->badge(fn($record) => $record?->scadenze()->count() ?? 0),

                    // Tab Udienze
                    Forms\Components\Tabs\Tab::make('Udienze')
                        ->icon('heroicon-m-scale')
                        ->schema([
                            Forms\Components\Section::make('Gestione Udienze')
                                ->columns([
                                    'default' => 1,
                                    'sm' => 1,
                                    'md' => 2,
                                    'lg' => 2,
                                ])
                                ->description('Gestisci le udienze della pratica')
                                ->schema([
                                    Forms\Components\Repeater::make('udienze')
                                        ->columnSpanFull()
                                        ->collapsible()
                                        ->collapsed()
                                        ->itemLabel(fn(array $state): ?string => Carbon::parse($state['data_ora'])->format('d/m/Y H:i') . ' - ' . $state['motivo'])
                                        ->relationship('udienze')
                                        ->defaultItems(0)
                                        ->schema(static::getUdienzeSchema()),
                                ]),
                        ])
                        ->badge(fn($record) => $record?->udienze()->count() ?? 0)
                        ->columnSpanFull(),

                    // Tab Documenti
                    Forms\Components\Tabs\Tab::make('Documenti')
                        ->icon('heroicon-m-document')
                        ->hidden()
                        ->schema([
                            Forms\Components\Section::make('Gestione Documenti')
                                ->columns([
                                    'default' => 1,
                                    'sm' => 1,
                                    'md' => 2,
                                    'lg' => 2,
                                ])
                                ->description('Aggiungi e gestisci i documenti della pratica')
                                ->schema(
                                    [
                                        Forms\Components\Repeater::make('documenti')
                                            ->grid([
                                                'default' => 1,
                                                'sm' => 1,
                                                'md' => 2,
                                                'lg' => 3,
                                            ])
                                            ->columnSpanFull()
                                            ->collapsible()
                                            ->collapsed()
                                            ->itemLabel(fn(array $state): ?string => strip_tags($state['descrizione'] ?? 'Nuovo documento'))
                                            ->relationship('documenti')
                                            ->schema([

                                                Forms\Components\FileUpload::make('file_path')
                                                    ->label('File')
                                                    ->disk('pratiche')
                                                    ->directory(config('documenti'))
                                                    ->maxSize(10240)
                                                    ->downloadable()
                                                    ->openable()
                                                    ->columnSpan(1),

                                                //FileUpload::make('file_path')
                                                //    ->label('Documento')
                                                //    ->required()
                                                //    ->directory('documenti')
                                                //    ->preserveFilenames()
                                                //    ->acceptedFileTypes([
                                                //        'application/pdf',
                                                //        'application/msword',
                                                //        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                                //        'image/jpeg',
                                                //        'image/png'
                                                //    ])
                                                //    ->maxSize(10240)
                                                //    ->live()
                                                //    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Components\FileUpload $component) {
                                                //     if (!$state) return;

                                                //     try {
                                                //         // Ottieni il file caricato
                                                //         $file = is_array($state) ? $state[0] : $state;

                                                //         $set('original_name', $file->getClientOriginalName());
                                                //         $set('mime_type', $file->getMimeType());
                                                //         $set('size', $file->getSize());
                                                //         $set('hash_file', hash_file('sha256', $file->getRealPath()));
                                                //         $set('user_id', auth()->id());

                                                //         Notification::make()
                                                //             ->success()
                                                //             ->title('File caricato')
                                                //             ->body('Documento caricato con successo')
                                                //             ->send();

                                                //     } catch (\Exception $e) {
                                                //         Notification::make()
                                                //             ->danger()
                                                //             ->title('Errore')
                                                //             ->body('Errore nel caricamento del file: ' . $e->getMessage())
                                                //             ->send();
                                                //     }
                                                // }),

                                                Select::make('categoria_id')
                                                    ->relationship('categoria', 'nome')
                                                    ->required()
                                                    ->createOptionForm([
                                                        TextInput::make('nome')
                                                            ->required()
                                                            ->maxLength(255),
                                                        TextInput::make('descrizione')
                                                            ->maxLength(255),
                                                    ]),

                                                TextInput::make('descrizione')
                                                    ->maxLength(255),

                                                // Campi nascosti per metadati
                                                TextInput::make('original_name')->hidden(),
                                                TextInput::make('mime_type')->hidden(),
                                                TextInput::make('size')->hidden(),
                                            ])
                                            ->defaultItems(0)
                                    ]
                                ),
                        ])
                        ->badge(fn($record) => $record?->documenti->count() ?? 0),


                    // Tab Parti
                    Forms\Components\Tabs\Tab::make('Parti')
                        ->icon('heroicon-m-users')
                        ->schema([
                            Forms\Components\Section::make('Parti Coinvolte')
                                ->columns([
                                    'default' => 1,
                                    'sm' => 1,
                                    'md' => 2,
                                    'lg' => 2,
                                ])
                                ->schema([
                                    Forms\Components\Select::make('assistiti')
                                        ->multiple()
                                        ->label('Assistiti')
                                        ->relationship(
                                            'assistiti',
                                            'id',
                                        )
                                        ->getOptionLabelFromRecordUsing(fn($record) => $record->getNomeCompletoAttribute())
                                        ->options(function () {
                                            // get only type assistito
                                            return
                                                Assistito::query()
                                                    ->assistiti()
                                                    ->get()
                                                    ->mapWithKeys(fn($assistito) => [$assistito->id => $assistito->getNomeCompletoAttribute()]);
                                        })
                                        ->createOptionForm(function () {
                                            return static::getCreateOptionFormSchema(Anagrafica::TYPE_ASSISTITO);
                                        })
                                        ->searchable(['nome', 'cognome', 'denominazione']),

                                    Forms\Components\Select::make('controparti')
                                        ->multiple()
                                        ->label('Controparti')
                                        ->relationship(
                                            'controparti',
                                            'id',
                                        )
                                        ->getOptionLabelFromRecordUsing(fn($record) => $record->getNomeCompletoAttribute())
                                        ->options(function () {
                                            return
                                                Controparte::query()
                                                    ->controparti()
                                                    ->get()
                                                    ->mapWithKeys(fn($controparte) => [$controparte->id => $controparte->getNomeCompletoAttribute()]);
                                        })
                                        ->createOptionForm(function () {
                                            return static::getCreateOptionFormSchema(Anagrafica::TYPE_CONTROPARTE);
                                        })
                                        ->searchable(['nome', 'cognome', 'denominazione']),
                                ]),
                        ]),

                    // Tab Note
                    Forms\Components\Tabs\Tab::make('Note')
                        ->icon('heroicon-m-pencil-square')
                        ->schema([
                            Forms\Components\Section::make('Gestione Note')
                                ->columns([
                                    'default' => 1,
                                    'sm' => 1,
                                    'md' => 2,
                                    'lg' => 2,
                                ])
                                ->description('Aggiungi e gestisci le note della pratica')
                                ->schema([
                                    Forms\Components\Repeater::make('note')
                                        ->relationship()
                                        ->schema([
                                            Forms\Components\Grid::make([
                                                'default' => 1,    // Una colonna su mobile
                                                'sm' => 1,         // Una colonna su schermi piccoli
                                                'md' => 2,         // Due colonne su tablet
                                                'lg' => 2,         // Due colonne su desktop
                                            ])
                                                ->schema(static::getNoteSchema()),
                                        ])
                                        ->defaultItems(0)
                                        ->collapsible()
                                        // Oggetto della nota - Visibilità - Data Creazione
                                        ->itemLabel(function (array $state): ?string {
                                            $created_at = (isset($state['created_at'])) ? Carbon::parse($state['created_at'])->format('d/m/Y H:i') : '';
                                            return $state['oggetto'] . ' - ' . $state['visibilita'] . ' - ' . $created_at;
                                        })
                                ]),
                        ])
                        ->badge(fn($record) => $record?->note()->count() ?? 0),
                ])
                ->activeTab(1)
                ->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_pratica')
                    ->label('Numero Pratica')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipologia')
                    ->label('Tipologia')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('team_id')
                    ->getStateUsing(function ($record) {
                        if($record->team_id == null) {
                            return 'Nessun gruppo';
                        }

                        return Team::find($record->team_id)->name;
                    })
                    ->badge()
                    ->color('info')
                    ->label('Gruppi')
                    ->searchable(),
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
            'index' => Pages\ListPraticas::route('/'),
            'create' => Pages\CreatePratica::route('/create'),
            'edit' => Pages\EditPratica::route('/{record}/edit'),
        ];
    }
}