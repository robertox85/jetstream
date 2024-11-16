<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pratica extends Model
{
    /** @use HasFactory<\Database\Factories\PraticaFactory> */
    use HasFactory;
    use LogsActivity;

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
        'team_id'
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
            ->withTimestamps()
            ->withTrashed();
    }

    // Relazione con il modello Assistito
    public function assistiti()
    {
        return $this->belongsToMany(Anagrafica::class, 'anagrafica_pratica')
            ->withPivot('tipo_relazione')
            ->withTimestamps()
            ->withTrashed()
            ->wherePivot('tipo_relazione', 'assistito');
    }

    // Relazione con il modello Controparte
    public function controparti()
    {
        return $this->belongsToMany(Anagrafica::class, 'anagrafica_pratica')
            ->withPivot('tipo_relazione')
            ->withTimestamps()
            ->withTrashed()
            ->wherePivot('tipo_relazione', 'controparte');
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
    public function generateNumeroPratica(string $prefisso = 'STD'): string
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
        $ultimaPratica = static::where('team_id', $this->team_id)
            ->whereYear('created_at', $anno)
            ->where('numero_pratica', 'like', "%/{$anno}/%")
            ->orderByRaw('CAST(SUBSTRING_INDEX(numero_pratica, "/", -1) AS UNSIGNED) DESC')
            ->first();

        if (!$ultimaPratica || !$ultimaPratica->numero_pratica) {
            $numeroProgressivo = 1;
        } else {
            // Estrae l'ultimo numero progressivo
            $parts = explode('/', $ultimaPratica->numero_pratica);
            $numeroProgressivo = intval(end($parts)) + 1;
        }

        // Formatta il numero pratica
        return sprintf(
            '%s/%s/%s/%s',
            $prefisso,
            strtoupper($teamName),
            $anno,
            str_pad($numeroProgressivo, 3, '0', STR_PAD_LEFT)
        );
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


    // Metodi di utilità per l'aggiunta di anagrafiche
    public function aggiungiAssistito(Anagrafica $anagrafica)
    {
        return $this->anagrafiche()->attach($anagrafica->id, [
            'tipo_relazione' => 'assistito'
        ]);
    }

    public function aggiungiControparte(Anagrafica $anagrafica)
    {
        return $this->anagrafiche()->attach($anagrafica->id, [
            'tipo_relazione' => 'controparte'
        ]);
    }

    // Metodi di utilità per la verifica
    public function isAssistito(Anagrafica $anagrafica)
    {
        return $this->assistiti()->where('anagrafiche.id', $anagrafica->id)->exists();
    }

    public function isControparte(Anagrafica $anagrafica)
    {
        return $this->controparti()->where('anagrafiche.id', $anagrafica->id)->exists();
    }
}
