<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anagrafica extends Model
{
    /** @use HasFactory<\Database\Factories\AnagraficaFactory> */
    use HasFactory;

    // Costanti per i tipi
    const TYPE_CONTROPARTE = 'controparte';
    const TYPE_ASSISTITO = 'assistito';

    const TIPO_PERSONA = 'Persona';
    const TIPO_AZIENDA = 'Azienda';

    protected $fillable = [
        'type',
        'tipo_utente',
        'denominazione',
        'nome',
        'cognome',
        'indirizzo',
        'codice_postale',
        'citta',
        'provincia',
        'telefono',
        'cellulare',
        'email',
        'pec',
        'codice_fiscale',
        'partita_iva',
        'codice_univoco_destinatario',
        'nota'
    ];

    // RELAZIONI

    public function pratiche()
    {
        return $this->belongsToMany(Pratica::class, 'anagrafica_pratica')
            ->withPivot('tipo_relazione')
            ->withTimestamps()
            ->withTrashed();
    }

    // SCOPE QUERY

    public function scopeAssistiti($query)
    {
        return $query->where('type', self::TYPE_ASSISTITO);
    }

    public function scopeControparti($query)
    {
        return $query->where('type', self::TYPE_CONTROPARTE);
    }

    public function scopePersoneFisiche($query)
    {
        return $query->where('tipo_utente', self::TIPO_PERSONA);
    }

    public function scopeAziende($query)
    {
        return $query->where('tipo_utente', self::TIPO_AZIENDA);
    }

    // ATTRIBUTI ACCESSORI

    // Nome completo per persone fisiche
    public function getNomeCompletoAttribute()
    {
        if ($this->tipo_utente === self::TIPO_PERSONA) {
            return trim("{$this->nome} {$this->cognome}");
        }
        return $this->denominazione;
    }

    // Indirizzo completo formattato
    public function getIndirizzoCompletoAttribute()
    {
        return trim("{$this->indirizzo} - {$this->codice_postale} {$this->citta} ({$this->provincia})");
    }

    // VALIDAZIONI

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($anagrafica) {
            // Validazione codice fiscale
            if ($anagrafica->codice_fiscale) {
                $anagrafica->codice_fiscale = strtoupper($anagrafica->codice_fiscale);
                if (!self::isValidCodiceFiscale($anagrafica->codice_fiscale)) {
                    throw new \Exception('Codice fiscale non valido');
                }
            }

            // Validazione partita IVA
            if ($anagrafica->partita_iva && !self::isValidPartitaIva($anagrafica->partita_iva)) {
                throw new \Exception('Partita IVA non valida');
            }

            // Uppercase provincia
            if ($anagrafica->provincia) {
                $anagrafica->provincia = strtoupper($anagrafica->provincia);
            }
        });
    }

}
