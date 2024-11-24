<?php

namespace App\Models;

use App\Traits\HasImportExportActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;


class Pratica extends Model
{
    /** @use HasFactory<\Database\Factories\PraticaFactory> */
    use HasFactory;
    use HasImportExportActions;
    use SoftDeletes;
    use Versionable;


    protected $versionable = ['nome', 'tipologia', 'competenza', 'ruolo_generale', 'giudice', 'stato', 'altri_riferimenti', 'priority', 'data_apertura', 'team_id', 'lavorazione', 'contabilita'];

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $table = 'pratiche';

    // Definizione degli stati possibili come costanti
    const STATO_APERTO = 'aperto';
    const STATO_CHIUSO = 'chiuso';
    const STATO_SOSPESO = 'sospeso';

    // Mass assignment protection
    protected $fillable = [
        'numero_pratica',
        'nome',
        'tipologia',
        'competenza',
        'ruolo_generale',
        'giudice',
        'stato',
        'altri_riferimenti',
        'priority',
        'data_apertura',
        'team_id',
        'lavorazione',
        'contabilita',
    ];

    // Cast automatici
    protected $casts = [
        'data_apertura' => 'date',
        'deleted_at' => 'datetime',
    ];

    // Relazione con il modello Team
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // Relazione con il modello Udienza
    public function udienze()
    {
        return $this->hasMany(Udienza::class)->withTrashed();
    }

    // Relazione con il modello Scadenza
    public function scadenze()
    {
        return $this->hasMany(Scadenza::class)->withTrashed();
    }

    // Relazione con il modello Documento
    public function documenti()
    {
        return $this->hasMany(Documento::class);
    }

    // Relazione con il modello Nota
    public function note()
    {
        return $this->hasMany(Nota::class)->withTrashed();
    }

    // Relazione con il modello Anagrafica
    public function anagrafiche()
    {
        return $this->belongsToMany(Anagrafica::class, 'anagrafica_pratica')
            ->withPivot('tipo_relazione')
            ->withTimestamps();
    }

    // Relazione con il modello Assistito
    public function assistiti()
    {
        return $this->belongsToMany(Assistito::class, 'anagrafica_pratica', 'pratica_id', 'anagrafica_id')
            ->withPivot('tipo_relazione')
            ->withTimestamps()
            ->wherePivot('tipo_relazione', 'assistito')
            ->withPivotValue('tipo_relazione', 'assistito'); // Aggiunge automaticamente il tipo
    }

// Relazione con il modello Controparte
    public function controparti()
    {
        return $this->belongsToMany(Controparte::class, 'anagrafica_pratica', 'pratica_id', 'anagrafica_id')
            ->withPivot('tipo_relazione')
            ->withTimestamps()
            ->wherePivot('tipo_relazione', 'controparte')
            ->withPivotValue('tipo_relazione', 'controparte'); // Aggiunge automaticamente il tipo
    }

    // Relazione con contabilita


// Modifica anche i metodi di utilità
    public function aggiungiAssistito(Assistito $assistito)
    {
        $this->assistiti()->attach($assistito->id);
    }

    public function aggiungiControparte(Controparte $controparte)
    {
        $this->controparti()->attach($controparte->id);
    }

    public function isAssistito(Assistito $assistito)
    {
        return $this->assistiti()->where('anagrafiche.id', $assistito->id)->exists();
    }

    public function isControparte(Controparte $controparte)
    {
        return $this->controparti()->where('anagrafiche.id', $controparte->id)->exists();
    }

    // Pratiche aperte
    public function scopeAperte($query)
    {
        return $query->where('stato', self::STATO_APERTO);
    }

    // Pratiche con scadenze in arrivo
    public function scopeConScadenzeImminenti($query, $giorni = 7)
    {
        return $query->whereHas('scadenze', function ($q) use ($giorni) {
            $q->whereBetween('data_ora', [now(), now()->addDays($giorni)]);
        });
    }

    // METODI DI UTILITÀ

    /**
     * Genera il numero pratica nel formato Prefisso/NomeTeam/Anno/NumeroProgressivo
     * Es: STD/CIVILE/2024/001
     *
     * @param string $prefisso Default 'STD'
     * @return string
     * @throws \Exception
     */
//    public function generateNumeroPratica(string $prefisso = 'STD', string $separatore = '-'): string
//    {
//        // Verifica che esista team_id
//        if (!$this->team_id) {
//            throw new \Exception('La pratica deve essere associata a un team per generare il numero pratica');
//        }
//
//        // Ottieni il nome del team direttamente
//        $teamName = Team::where('id', $this->team_id)->value('name');
//        if (!$teamName) {
//            throw new \Exception('Team non trovato');
//        }
//
//        $anno = date('Y');
//
//        // Trova l'ultimo numero progressivo per questo team e anno
//        // Trova l'ultimo numero progressivo per questo team e anno, inclusi i soft deleted
//        $ultimoNumero = static::withTrashed()
//            ->where('team_id', $this->team_id)
//            ->whereYear('created_at', $anno)
//            ->max('numero_pratica');
//
//        // Se non ci sono pratiche per questo team e anno, inizia da 1
//        if (!$ultimoNumero) {
//            $numeroProgressivo = 1;
//        } else {
//            // Estrai il numero progressivo dall'ultimo numero pratica
//            $numeroProgressivo = (int)substr($ultimoNumero, -3);
//            $numeroProgressivo++;
//        }
//
//        // Formatta il numero progressivo con 3 cifre
//        $numeroProgressivo = str_pad($numeroProgressivo, 3, '0', STR_PAD_LEFT);
//
//        return "{$prefisso}{$separatore}{$teamName}{$separatore}{$anno}{$separatore}{$numeroProgressivo}";
//    }


    /**
     * Genera il numero pratica nel formato Prefisso/NomeTeam/Anno/NumeroProgressivo
     * Es: STD/CIVILE/2024/001
     *
     * @param string $prefisso Default 'STD'
     * @return string
     * @throws \Exception
     */
    protected static function generateNumeroPratica($pratica): string
    {
        try {
            $config = config('pratica.numero_pratica');

            if (!$config) {
                throw new \RuntimeException('Configurazione numero pratica non trovata');
            }

            Log::debug('Inizio generazione numero pratica', [
                'pratica_id' => $pratica->id,
                'formato' => $config['formato'],
                'team_id' => $pratica->team_id
            ]);

            // Se il formato è custom, usa il pattern personalizzato
            if ($config['formato'] === 'custom') {
                return static::generateCustomFormat($pratica, $config['pattern_custom']);
            }

            $numeroBuilder = new class($pratica, $config) {
                private $pratica;
                private $config;
                private $parts = [];
                private $separatore;

                public function __construct($pratica, $config)
                {
                    $this->pratica = $pratica;
                    $this->config = $config;
                    $this->separatore = $config['separatore'];
                }

                public function build(): string
                {
                    $this->buildPartsBasedOnFormat();
                    $this->addProgressiveNumber();
                    return $this->combinePartsToString();
                }

                private function buildPartsBasedOnFormat()
                {
                    switch ($this->config['formato']) {
                        case 'tipo':
                            $this->addTipoPart();
                            break;
                        case 'team':
                            $this->addTeamPart();
                            break;
                        case 'mensile':
                            $this->addDateParts();
                            break;
                    }
                }

                private function addTipoPart()
                {
                    $prefissi = $this->config['prefissi_tipo']['prefissi'];
                    $defaultPrefix = $this->config['prefissi_tipo']['default'];
                    $this->parts[] = $prefissi[$this->pratica->tipologia] ?? $defaultPrefix;
                }

                private function addTeamPart()
                {
                    if (!$this->pratica->team_id) {
                        Log::warning('Pratica senza team', ['pratica_id' => $this->pratica->id]);
                        $this->parts[] = 'GEN';
                        return;
                    }
                    $teamPrefix = Team::find($this->pratica->team_id)->name ?? 'TEAM';
                    $this->parts[] = 'G-' . $teamPrefix;
                }

                private function addDateParts()
                {
                    if ($this->config['componenti']['anno']['includi']) {
                        $this->parts[] = date($this->config['componenti']['anno']['formato']);
                    }
                    if ($this->config['componenti']['mese']['includi']) {
                        $this->parts[] = date($this->config['componenti']['mese']['formato']);
                    }
                }

                private function addProgressiveNumber()
                {
                    $pattern = $this->createSearchPattern();
                    $query = $this->buildProgressiveQuery($pattern);
                    $ultimaPratica = $query->orderBy('id', 'desc')->first();

                    $numero = $this->calculateNextNumber($ultimaPratica);
                    $this->parts[] = str_pad($numero, $this->config['lunghezza_numero'], '0', STR_PAD_LEFT);
                }

                private function createSearchPattern(): string
                {
                    return implode($this->separatore, $this->parts) . (!empty($this->parts) ? $this->separatore : '');
                }

                private function buildProgressiveQuery($pattern)
                {
                    $query = $this->pratica::withTrashed()
                        ->where('numero_pratica', 'like', $pattern . '%');

                    return $this->applyResetLogic($query);
                }

                private function applyResetLogic($query)
                {
                    switch ($this->config['reset_contatore']['frequenza']) {
                        case 'annuale':
                            return $query->whereYear('created_at', Carbon::now()->year);
                        case 'mensile':
                            return $query->whereYear('created_at', Carbon::now()->year)
                                ->whereMonth('created_at', Carbon::now()->month);
                        default:
                            return $query;
                    }
                }

                private function calculateNextNumber($ultimaPratica)
                {
                    if (!$ultimaPratica) {
                        return $this->config['numero_partenza'];
                    }

                    // Estrai l'ultimo numero usando una regex più precisa
                    if (preg_match('/-(\d{3})$/', $ultimaPratica->numero_pratica, $matches)) {
                        $lastNumber = (int)$matches[1];
                        // Trova il numero più alto tra tutte le pratiche
                        $maxNumber = static::withTrashed()
                            ->whereYear('created_at', Carbon::now()->year)
                            ->get()
                            ->map(function ($pratica) {
                                if (preg_match('/-(\d{3})$/', $pratica->numero_pratica, $matches)) {
                                    return (int)$matches[1];
                                }
                                return 0;
                            })
                            ->max();

                        return max($lastNumber, $maxNumber) + 1;
                    }

                    return $this->config['numero_partenza'];
                }

                private function combinePartsToString(): string
                {
                    return implode($this->separatore, $this->parts);
                }
            };

            $numeroPratica = $numeroBuilder->build();

            Log::info('Numero pratica generato con successo', [
                'pratica_id' => $pratica->id,
                'numero' => $numeroPratica
            ]);

            return $numeroPratica;

        } catch (\Exception $e) {
            Log::error('Errore nella generazione del numero pratica', [
                'pratica_id' => $pratica->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \RuntimeException('Errore nella generazione del numero pratica: ' . $e->getMessage());
        }
    }

    protected static function generateCustomFormat($pratica, string $pattern): string
    {
        try {
            $config = config('pratica.numero_pratica');

            $replacementBuilder = new class($pratica, $config) {
                private $pratica;
                private $config;
                private $replacements = [];

                public function __construct($pratica, $config)
                {
                    $this->pratica = $pratica;
                    $this->config = $config;
                }

                public function build(): array
                {
                    $this->addDateReplacements();
                    $this->addTipoReplacement();
                    $this->addTeamReplacement();
                    $this->addProgressiveReplacement();
                    return $this->replacements;
                }

                private function addDateReplacements()
                {
                    $this->replacements['{anno}'] = $this->config['componenti']['anno']['includi']
                        ? date($this->config['componenti']['anno']['formato'])
                        : '';

                    $this->replacements['{mese}'] = $this->config['componenti']['mese']['includi']
                        ? date($this->config['componenti']['mese']['formato'])
                        : '';
                }

                private function addTipoReplacement()
                {
                    $prefissi = $this->config['prefissi_tipo']['prefissi'];
                    $defaultPrefix = $this->config['prefissi_tipo']['default'];

                    $this->replacements['{tipo}'] = isset($this->pratica->tipologia)
                        ? ($prefissi[$this->pratica->tipologia] ?? $defaultPrefix)
                        : $defaultPrefix;
                }

                private function addTeamReplacement()
                {
                    if (!$this->pratica->team_id) {
                        $this->replacements['{team}'] = 'GEN';
                        return;
                    }

                    $team = Team::find($this->pratica->team_id);
                    $this->replacements['{team}'] = $team ? $team->name : 'GEN';
                }

                private function addProgressiveReplacement()
                {
                    $replacements = [];

                    DB::transaction(function () use (&$replacements) {
                        $basePattern = str_replace(
                            array_keys($this->replacements),
                            array_values($this->replacements),
                            $this->config['pattern_custom']
                        );

                        $query = $this->buildProgressiveQuery($basePattern);

                        // Aggiungi il lock
                        $ultimaPratica = $query->lockForUpdate()->orderBy('id', 'desc')->first();

                        $numero = $this->calculateNextNumber($ultimaPratica);

                        // Verifica che il numero generato non esista già
                        $numeroGenerato = str_pad(
                            $numero,
                            $this->config['componenti']['progressivo']['lunghezza'],
                            '0',
                            STR_PAD_LEFT
                        );

                        $this->replacements['{numero}'] = $numeroGenerato;

                        // Verifica che il numero completo non esista
                        $numeroCompleto = str_replace(
                            array_keys($this->replacements),
                            array_values($this->replacements),
                            $this->config['pattern_custom']
                        );

                        // Se esiste già, incrementa fino a trovare un numero disponibile
                        while ($this->pratica::withTrashed()->where('numero_pratica', $numeroCompleto)->exists()) {
                            $numero++;
                            $numeroGenerato = str_pad(
                                $numero,
                                $this->config['componenti']['progressivo']['lunghezza'],
                                '0',
                                STR_PAD_LEFT
                            );
                            $this->replacements['{numero}'] = $numeroGenerato;
                            $numeroCompleto = str_replace(
                                array_keys($this->replacements),
                                array_values($this->replacements),
                                $this->config['pattern_custom']
                            );
                        }

                        $replacements = $numeroGenerato;
                    });

                    $this->replacements['{numero}'] = $replacements;
                }

                private function buildProgressiveQuery($basePattern)
                {
                    // Rimuovi il placeholder {numero} dal pattern per la ricerca
                    $searchPattern = str_replace('{numero}', '', $basePattern);

                    $query = $this->pratica::withTrashed()
                        ->where('numero_pratica', 'like', $searchPattern . '%');

                    return $this->applyProgressiveLogic($query);
                }


                private function applyProgressiveLogic($query)
                {
                    switch ($this->config['componenti']['progressivo']['tipo']) {
                        case 'annuale':
                            return $query->whereYear('created_at', Carbon::now()->year);
                        case 'mensile':
                            return $query->whereYear('created_at', Carbon::now()->year)
                                ->whereMonth('created_at', Carbon::now()->month);
                        default:
                            return $query;
                    }
                }

                private function calculateNextNumber($ultimaPratica)
                {
                    return $ultimaPratica
                        ? (intval(substr($ultimaPratica->numero_pratica, -$this->config['componenti']['progressivo']['lunghezza'])) + 1)
                        : $this->config['numero_partenza'];
                }
            };

            $replacements = $replacementBuilder->build();

            $numeroPratica = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $pattern
            );

            Log::info('Numero pratica custom generato con successo', [
                'pratica_id' => $pratica->id,
                'pattern' => $pattern,
                'numero' => $numeroPratica
            ]);

            return $numeroPratica;

        } catch (\Exception $e) {
            Log::error('Errore nella generazione del numero pratica custom', [
                'pratica_id' => $pratica->id,
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Errore nella generazione del numero pratica custom: ' . $e->getMessage());
        }
    }

    public function teams(): BelongsTo
    {
        return $this->belongsTo(Team::class);

    }

    /**
     * Override del metodo booted per generare automaticamente il numero pratica
     */
    protected static function booted()
    {
        // Prima del salvataggio, se è una nuova pratica
        static::creating(function ($pratica) {
            if (empty($pratica->numero_pratica)) {
                $pratica->numero_pratica = $pratica->generateNumeroPratica($pratica);
            }
        });

        // Soft delete dei figli
        static::deleting(function ($pratica) {
            if (!$pratica->isForceDeleting()) {
                $pratica->udienze()->delete();
                $pratica->scadenze()->delete();
                $pratica->note()->delete();
            }
        });

        static::forceDeleting(function ($pratica) {
            DB::table('anagrafica_pratica')->where('pratica_id', $pratica->id)->delete();
        });

        // Ripristino dei figli
        static::restoring(function ($pratica) {
            $pratica->udienze()->withTrashed()->restore();
            $pratica->scadenze()->withTrashed()->restore();
            $pratica->note()->withTrashed()->restore();

            // Ripristino anche le anagrafiche
            DB::table('anagrafica_pratica')
                ->where('pratica_id', $pratica->id)
                ->update(['deleted_at' => null]);
        });


        // Regenera il numero pratica se il team_id cambia
        static::updating(function ($pratica) {
            if ($pratica->isDirty('team_id')) {
                // TODO: Verificare se è necessario rigenerare il numero pratica
                // $pratica->numero_pratica = $pratica->generateNumeroPratica($pratica);
            }
        });

    }

    /**
     * Verifica se un numero pratica è già utilizzato
     *
     * @param string $numeroPratica
     * @return bool
     */
    public static function isNumeroPraticaUsed($numeroPratica)
    {
        return static::where('numero_pratica', $numeroPratica)->exists();
    }


    public function scopeOrderByTeamENumero($query, $direction = 'asc')
    {
        return $query->orderByRaw("
        SUBSTRING_INDEX(SUBSTRING_INDEX(numero_pratica, '-', 2), '-', -1) {$direction}, -- Team
        SUBSTRING_INDEX(SUBSTRING_INDEX(numero_pratica, '-', -2), '-', 1) {$direction}, -- Anno
        CAST(SUBSTRING_INDEX(numero_pratica, '-', -1) AS UNSIGNED) {$direction} -- Numero progressivo
    ");
    }


    public function scopeOrderByNumeroProgressivo($query, $direction = 'asc')
    {
        return $query->orderByRaw("CAST(SUBSTRING_INDEX(numero_pratica, '-', -1) AS UNSIGNED) {$direction}");
    }

// Oppure se vuoi ordinare anche per anno
    public function scopeOrderByNumeroCompleto($query, $direction = 'asc')
    {
        return $query->orderByRaw("
        SUBSTRING_INDEX(SUBSTRING_INDEX(numero_pratica, '-', -2), '-', 1) {$direction},
        CAST(SUBSTRING_INDEX(numero_pratica, '-', -1) AS UNSIGNED) {$direction}
    ");
    }

}
