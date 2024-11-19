<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pratica extends Model
{
    /** @use HasFactory<\Database\Factories\PraticaFactory> */
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

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
        return $this->hasMany(Udienza::class);
    }

    // Relazione con il modello Scadenza
    public function scadenze()
    {
        return $this->hasMany(Scadenza::class);
    }

    // Relazione con il modello Documento
    public function documenti()
    {
        return $this->hasMany(Documento::class);
    }

    // Relazione con il modello Nota
    public function note()
    {
        return $this->hasMany(Nota::class);
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
    public function contabilita()
    {
        return $this->hasMany(Contabilita::class);
    }

    // Relazione con lavorazioni
    public function lavorazioni()
    {
        return $this->hasMany(Lavorazione::class);
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

    // METODI DI UTILITÀ

    /**
     * Genera il numero pratica nel formato Prefisso/NomeTeam/Anno/NumeroProgressivo
     * Es: STD/CIVILE/2024/001
     *
     * @param string $prefisso Default 'STD'
     * @return string
     * @throws \Exception
     */
    public function generateNumeroPratica(string $prefisso = 'STD', string $separatore = '-'): string
    {
        // Verifica che esista team_id
        if (!$this->team_id) {
            throw new \Exception('La pratica deve essere associata a un team per generare il numero pratica');
        }

        // Ottieni il nome del team direttamente
        $teamName = Team::where('id', $this->team_id)->value('name');
        if (!$teamName) {
            throw new \Exception('Team non trovato');
        }

        $anno = date('Y');

        // Trova l'ultimo numero progressivo per questo team e anno
        // Trova l'ultimo numero progressivo per questo team e anno, inclusi i soft deleted
        $ultimoNumero = static::withTrashed()
            ->where('team_id', $this->team_id)
            ->whereYear('created_at', $anno)
            ->max('numero_pratica');

        // Se non ci sono pratiche per questo team e anno, inizia da 1
        if (!$ultimoNumero) {
            $numeroProgressivo = 1;
        } else {
            // Estrai il numero progressivo dall'ultimo numero pratica
            $numeroProgressivo = (int)substr($ultimoNumero, -3);
            $numeroProgressivo++;
        }

        // Formatta il numero progressivo con 3 cifre
        $numeroProgressivo = str_pad($numeroProgressivo, 3, '0', STR_PAD_LEFT);

        return "{$prefisso}{$separatore}{$teamName}{$separatore}{$anno}{$separatore}{$numeroProgressivo}";
    }

    /**
     * Override del metodo boot per generare automaticamente il numero pratica
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pratica) {
            if (empty($pratica->numero_pratica)) {
                $pratica->numero_pratica = $pratica->generateNumeroPratica();
            }
        });


        // Quando una pratica viene soft-deleted
        static::deleting(function($pratica) {
            // Elimina (soft delete) tutte le relazioni
            $pratica->note()->delete();
            $pratica->scadenze()->delete();
            $pratica->udienze()->delete();
            $pratica->documenti()->delete();
        });

        // Quando una pratica viene ripristinata
        static::restored(function($pratica) {
            // Ripristina tutte le relazioni
            $pratica->note()->withTrashed()->restore();
            $pratica->scadenze()->withTrashed()->restore();
            $pratica->udienze()->withTrashed()->restore();
            $pratica->documenti()->withTrashed()->restore();
        });

        // Quando una pratica viene eliminata definitivamente
        static::forceDeleting(function($pratica) {
            // Elimina definitivamente tutte le relazioni
            $pratica->note()->forceDelete();
            $pratica->scadenze()->forceDelete();
            $pratica->udienze()->forceDelete();
            $pratica->documenti()->forceDelete();
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

    /**
     * Rigenera il numero pratica
     *
     * @param string $prefisso
     * @return string
     * @throws \Exception
     */
    public function rigeneraNumeroPratica(string $prefisso = 'STD'): string
    {
        $nuovoNumero = $this->generateNumeroPratica($prefisso);
        $this->update(['numero_pratica' => $nuovoNumero]);
        return $nuovoNumero;
    }

}
