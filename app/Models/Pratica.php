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
use Spatie\MediaLibrary\InteractsWithMedia;


class Pratica extends Model
{
    /** @use HasFactory<\Database\Factories\PraticaFactory> */
    use HasFactory;
    use HasImportExportActions;
    use SoftDeletes;
    use Versionable;
use InteractsWithMedia;

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
    public function eventi()
    {
        return $this->hasMany(Evento::class);
    }

    public function scadenze()
    {
        return $this->eventi()->where('tipo', Evento::TIPO_SCADENZA);
    }

    public function udienze()
    {
        return $this->eventi()->where('tipo', Evento::TIPO_UDIENZA);
    }

    public function appuntamenti()
    {
        return $this->eventi()->where('tipo', Evento::TIPO_APPUNTAMENTO);
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

    // utenti extra
    public function utenti_extra()
    {
        return $this->belongsToMany(User::class, 'pratiche_utenti', 'pratica_id', 'user_id')
            ->withTimestamps();
    }


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


    public function teams(): BelongsTo
    {
        return $this->belongsTo(Team::class);

    }


    /**
     * @throws \Exception
     */
    public function generaNumeroPratica($pratica): string
    {
        DB::beginTransaction();
        try {
            $teamName = Team::findOrFail($pratica->team_id)->name ?? 'NoTeam';
            $teamName = preg_replace('/[^A-Za-z0-9]/', '', $teamName);
            $anno = Carbon::parse($pratica->created_at)->year;

            $lastNumber = Pratica::withTrashed()
                ->whereYear('created_at', $anno)
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $progressivo = $lastNumber
                ? (intval(explode('-', $lastNumber->numero_pratica)[3]) + 1)
                : 1;

            if ($progressivo > 999) {
                throw new \RuntimeException("Max number reached for year {$anno}");
            }

            $numeroPratica = sprintf(
                'STD-%s-%s-%03d',
                $teamName,
                $anno,
                $progressivo
            );

            if (strlen($numeroPratica) > 100) {
                throw new \RuntimeException('Number too long');
            }

            // Check for duplicates
            if (Pratica::withTrashed()
                ->where('numero_pratica', $numeroPratica)
                ->lockForUpdate()
                ->exists()) {
                throw new \RuntimeException('Duplicate number detected');
            }

            DB::commit();
            return $numeroPratica;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function aggiornaNumeroPratica(?Pratica $pratica = null): void
    {
        DB::beginTransaction();
        try {
            $pratica = $pratica ?? $this;

            $parts = explode('-', $pratica->numero_pratica);

            if (count($parts) !== 4) {
                throw new \InvalidArgumentException('Invalid number format');
            }

            $teamName = Team::findOrFail($pratica->team_id)->name ?? 'NoTeam';
            $parts[1] = preg_replace('/[^A-Za-z0-9]/', '', $teamName);

            $numeroPratica = implode('-', $parts);

            if (Pratica::withTrashed()
                ->where('numero_pratica', $numeroPratica)
                ->where('id', '!=', $pratica->id)
                ->exists()) {
                throw new \RuntimeException('Duplicate number');
            }

            DB::table('pratiche')
                ->where('id', $pratica->id)
                ->update(['numero_pratica' => $numeroPratica]);


            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Override del metodo booted per generare automaticamente il numero pratica
     */
    protected static function booted()
    {
        // Prima del salvataggio, se è una nuova pratica
        static::creating(function ($pratica) {
            if (empty($pratica->numero_pratica)) {
                $pratica->numero_pratica = $pratica->generaNumeroPratica($pratica);
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
        static::updated(function ($pratica) {
            if ($pratica->isDirty('team_id')) {
                $pratica->aggiornaNumeroPratica($pratica);
            }
        });

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
