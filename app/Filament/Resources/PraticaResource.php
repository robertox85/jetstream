<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PraticaResource\Pages;


use App\Models\Anagrafica;

use App\Models\Assistito;
use App\Models\Controparte;
use App\Models\Pratica;
use App\Models\Team;
use App\Models\User;
use App\Traits\HasAnagraficaForm;
use App\Traits\HasEventoForm;
use App\Traits\HasPraticaForm;
use App\Traits\HasTeamAuthorizationScope;
use DiscoveryDesign\FilamentGaze\Forms\Components\GazeBanner;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\ButtonAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function Livewire\wrap;

class PraticaResource extends Resource
{

    use HasPraticaForm, HasAnagraficaForm, HasEventoForm;

    protected static ?string $model = Pratica::class;

    //  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Pratica';
    protected static ?string $pluralModelLabel = 'Pratiche';
    protected static ?string $slug = 'pratiche';

    protected static ?string $navigationGroup = 'Gestione Pratiche';

    use HasTeamAuthorizationScope;

    public static function getEloquentQuery(): Builder
    {
        return static::getTeamBasedQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make('')
                    ->schema([
                        GazeBanner::make()
                            ->columnSpanFull()
                            ->lock()
                            ->hideOnCreate()
                            ->hidden(function ($record) {
                                return auth()->user()->hasExtraPermission($record);
                            })
                            ->canTakeControl(true),
                    ]),
                Forms\Components\Tabs::make('Pratica')
                    ->tabs([
                        // Informazioni Principali
                        Forms\Components\Tabs\Tab::make('Informazioni Principali')
                            ->disabledOn('view')
                            ->icon('heroicon-m-information-circle')
                            ->schema(static::getInformazioniPrincipaliSchema()),

                        // Tab Media
                        Forms\Components\Tabs\Tab::make('Documenti')
                            ->visibleOn(['edit'])
                            ->icon('heroicon-m-document')

                            ->schema([


                                Forms\Components\View::make('components.file-browser'),
                                // Forms\Components\View::make('vendor.laravel-filemanager.use'),
                                // Forms\Components\Actions::make([
                                //     Forms\Components\Actions\Action::make('Generate excerpt')
                                //         ->action(fn () => redirect('/laravel-filemanager?type=file'))
                                // ]),
                            ]),


                        // Tab Scadenze
                        Forms\Components\Tabs\Tab::make('Scadenze')
                            ->visibleOn(['create', 'edit'])
                            ->icon('heroicon-m-calendar')
                            ->schema([
                                Forms\Components\Repeater::make('scadenze')
                                    ->relationship('scadenze')
                                    ->columnSpanFull()
                                    ->label('')
                                    ->collapsible()
                                    ->collapsed()
                                    ->itemLabel(function (array $state): ?string {
                                        $data = (isset($state['data'])) ? Carbon::parse($state['data'])->format('d/m/Y') : '';
                                        return $state['motivo'] . ' - ' . $data;
                                    })
                                    ->defaultItems(0)
                                    ->addActionLabel('Aggiungi scadenza')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema(static::getEventoForm('scadenza'))
                                    ]),
                            ])
                            ->badge(fn($record) => $record?->scadenze()->count() ?? 0),

                        // Tab Udienze
                        Forms\Components\Tabs\Tab::make('Udienze')
                            ->visibleOn(['create', 'edit'])
                            ->icon('heroicon-m-scale')
                            ->schema([
                                Forms\Components\Repeater::make('udienze')
                                    ->relationship('udienze')
                                    ->columnSpanFull()
                                    ->label('')
                                    ->collapsible()
                                    ->collapsed()
                                    ->itemLabel(function (array $state): ?string {
                                        $data = (isset($state['data'])) ? Carbon::parse($state['data'])->format('d/m/Y') : '';
                                        return $state['motivo'] . ' - ' . $data;
                                    })
                                    ->defaultItems(0)
                                    ->addActionLabel('Aggiungi udienza')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema(static::getEventoForm('udienza'))
                                    ]),
                            ])
                            ->badge(fn($record) => $record?->udienze()->count() ?? 0)
                            ->columnSpanFull(),


                        // Tab Appuntamenti
                        Forms\Components\Tabs\Tab::make('Appuntamenti')
                            ->visibleOn(['create', 'edit'])
                            ->icon('heroicon-m-calendar')
                            ->schema([
                                Forms\Components\Repeater::make('appuntamenti')
                                    ->relationship('appuntamenti')
                                    ->columnSpanFull()
                                    ->label('')
                                    ->collapsible()
                                    ->collapsed()
                                    ->itemLabel(function (array $state): ?string {
                                        $data = (isset($state['data'])) ? Carbon::parse($state['data'])->format('d/m/Y') : '';
                                        return $state['motivo'] . ' - ' . $data;
                                    })
                                    ->defaultItems(0)
                                    ->addActionLabel('Aggiungi appuntamento')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema(static::getEventoForm('appuntamento'))
                                    ]),
                            ])
                            ->badge(fn($record) => $record?->appuntamenti()->count() ?? 0)
                            ->columnSpanFull(),

                        // Tab Documenti
                        // TODO: Implementare la gestione dei documenti via FileUpload FTP
                        Forms\Components\Tabs\Tab::make('Documenti')
                            ->visibleOn(['create', 'edit'])
                            ->icon('heroicon-m-document')
                            ->hidden()
                            ->schema([
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
                                    ->addActionLabel('Aggiungi documento')
                                    ->defaultItems(0)
                            ])
                            ->badge(fn($record) => $record?->documenti->count() ?? 0),


                        // Tab Parti
                        Forms\Components\Tabs\Tab::make('Parti')
                            ->visibleOn(['create', 'edit'])
                            ->icon('heroicon-m-users')
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

                        // Tab Note
                        Forms\Components\Tabs\Tab::make('Note')
                            ->visibleOn(['create', 'edit'])
                            ->icon('heroicon-m-pencil-square')
                            ->schema([
                                Forms\Components\Repeater::make('note')
                                    ->relationship()
                                    ->schema(static::getNoteSchema())
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->collapsed()
                                    ->addActionLabel('Aggiungi nota')
                                    // Oggetto della nota - Visibilità - Data Creazione
                                    ->itemLabel(function (array $state): ?string {
                                        $created_at = (isset($state['created_at'])) ? Carbon::parse($state['created_at'])->format('d/m/Y H:i') : '';
                                        return $state['oggetto'] . ' - ' . $state['visibilita'] . ' - ' . $created_at;
                                    })
                            ])
                            ->badge(fn($record) => $record?->note()->count() ?? 0),


                        // Tab Lavorazioni
                        Forms\Components\Tabs\Tab::make('Lavorazioni')
                            ->visibleOn(['create', 'edit'])
                            ->icon('heroicon-m-cog')
                            ->schema([
                                // Make a Textarea
                                Forms\Components\RichEditor::make('lavorazione')
                                    ->label('Lavorazione')
                                    ->placeholder('Inserisci una descrizione')
                                    ->toolbarButtons([
                                        'blockquote',
                                        'bold',
                                        'bulletList',
                                        'codeBlock',
                                        'h2',
                                        'h3',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'underline',
                                        'undo',
                                    ])
                                    ->columnSpanFull(),

                            ]),

                        // Tab Lavorazioni
                        Forms\Components\Tabs\Tab::make('Contabilità')
                            ->visibleOn(['create', 'edit'])
                            ->icon('heroicon-o-currency-euro')
                            ->schema([
                                // Make a Textarea
                                Forms\Components\RichEditor::make('contabilita')
                                    ->label('Contabilità')
                                    ->placeholder('Inserisci una descrizione')
                                    ->toolbarButtons([
                                        'blockquote',
                                        'bold',
                                        'bulletList',
                                        'codeBlock',
                                        'h2',
                                        'h3',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'underline',
                                        'undo',
                                    ])
                                    ->columnSpanFull(),
                            ]),


                    ])
                    ->activeTab(1)
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {

        //  'numero_pratica',
        //  'nome',
        //  'tipologia',
        //  'competenza',
        //  'ruolo_generale',
        //  'giudice',
        //  'stato',
        //  'altri_riferimenti',

        //  'data_apertura',
        //  'team_id',
        //  'lavorazione',
        //  'contabilita',
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([100, 150, 'all'])
            ->defaultPaginationPageOption(100)
            ->columns([
                Tables\Columns\TextColumn::make('numero_pratica')
                    ->label('Numero Pratica')
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByTeamENumero($direction);
                    }),
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipologia')
                    ->label('Tipologia')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('competenza')
                    ->label('Competenza')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ruolo_generale')
                    ->label('Ruolo Generale')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('giudice')
                    ->label('Giudice')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stato')
                    ->label('Stato')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('altri_riferimenti')
                    ->label('Altri Riferimenti')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable()
                    ->wrap()
                    ->limit(20)
                    ->sortable(),


                Tables\Columns\TextColumn::make('lavorazione')
                    ->label('Lavorazione')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable()
                    ->wrap()
                    ->html()
                    ->limit(50)
                    ->sortable(),

                Tables\Columns\TextColumn::make('contabilita')
                    ->label('Contabilità')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable()
                    ->wrap()
                    ->html()
                    ->limit(50)
                    ->sortable(),


                Tables\Columns\TextColumn::make('team_id')
                    ->getStateUsing(function ($record) {
                        if ($record->team_id == null) {
                            return 'Nessun gruppo';
                        }
                        return Team::find($record->team_id)->name;
                    })
                    ->badge()
                    ->color('info')
                    ->label('Gruppi')
                    ->searchable(),

                // created_at
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data creazione')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
            ])
            ->filters([
                // hidden if cliente
                Tables\Filters\TrashedFilter::make()
                ->hidden(function () { return auth()->user()->hasRole('Cliente'); }),


                Tables\Filters\SelectFilter::make('team_id')
                    ->hidden(function () { return auth()->user()->hasRole('Cliente'); })
                    ->label('Gruppo')
                    ->options(
                        Team::all()->pluck('name', 'id')
                    )
                    ->searchable(),

                Tables\Filters\SelectFilter::make('tipologia')
                    ->hidden(function () { return auth()->user()->hasRole('Cliente'); })
                    ->label('Tipologia')
                    ->options(config('pratica-form.tipologie'))
                    ->preload()
                    ->searchable(),

                Tables\Filters\SelectFilter::make('stato')
                    ->hidden(function () { return auth()->user()->hasRole('Cliente'); })
                    ->label('Stato')
                    ->options(config('pratica-form.stati'))
                    ->preload()
                    ->searchable(),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ViewAction::make()
                    ->visible(function ($record) {
                        return auth()->user()->hasExtraPermission($record);
                    })
                ,
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'revisions' => Pages\RevisionsPratica::route('/{record}/revisions'),

        ];
    }
}
