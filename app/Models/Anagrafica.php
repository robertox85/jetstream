<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Anagrafica extends Model
{
    /** @use HasFactory<\Database\Factories\AnagraficaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'anagrafiche';

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
            ->withPivot(['tipo_relazione', 'deleted_at'])
            ->withTimestamps();
    }

    // Metodo per ottenere solo le pratiche non cancellate
    public function praticheAttive()
    {
        return $this->pratiche()
            ->whereNull('anagrafica_pratica.deleted_at');
    }

    // Metodo per il force delete con conferma
    public function forceDeleteWithRelations()
    {
        DB::transaction(function() {
            // Rimuovi tutte le relazioni pivot
            $this->pratiche()->detach();
            // Force delete dell'anagrafica
            $this->forceDelete();
        });
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

    protected static function  booted()
    {

    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function($anagrafica) {
            DB::transaction(function() use ($anagrafica) {
                // Se è un soft delete
                if (!$anagrafica->isForceDeleting()) {
                    // Soft delete delle relazioni pivot
                    DB::table('anagrafica_pratica')
                        ->where('anagrafica_id', $anagrafica->id)
                        ->update(['deleted_at' => now()]);
                } else {
                    // Se è un force delete, rimuovi completamente le relazioni
                    $anagrafica->pratiche()->detach();
                }
            });
        });

        static::restored(function($anagrafica) {
            // Ripristina le relazioni pivot
            DB::table('anagrafica_pratica')
                ->where('anagrafica_id', $anagrafica->id)
                ->whereNotNull('deleted_at')
                ->update(['deleted_at' => null]);
        });


        static::saving(function ($anagrafica) {

            $esiste = static::where('nome', $anagrafica->nome)
                ->where('cognome', $anagrafica->cognome)
                ->where('denominazione', $anagrafica->denominazione)
                ->where('id', '!=', $anagrafica->id)
                ->exists();

            if ($esiste) {
                throw new \Exception('Anagrafica già esistente');
            }

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

    public static function isValidCodiceFiscale($cf): bool
    {
        // Per ora ritorna sempre true
        return true;

        $cf = strtoupper($cf);

        if (strlen($cf) != 16) {
            return false;
        }

        // Regex per validare il formato
        $pattern = '/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/';
        if (!preg_match($pattern, $cf)) {
            return false;
        }

        // Qui puoi aggiungere ulteriore logica per il calcolo del carattere di controllo
        // ... codice per il calcolo del check digit ...

        return true;
    }

    public static function isValidPartitaIva($pi): bool
    {
        // Per ora ritorna sempre true
        return true;

        if (strlen($pi) != 11) {
            return false;
        }

        if (!is_numeric($pi)) {
            return false;
        }

        $s = 0;
        for ($i = 0; $i <= 9; $i += 2) {
            $s += ord($pi[$i]) - ord('0');
        }

        for ($i = 1; $i <= 9; $i += 2) {
            $c = 2 * (ord($pi[$i]) - ord('0'));
            if ($c > 9) {
                $c = $c - 9;
            }
            $s += $c;
        }

        if ((10 - $s % 10) % 10 != ord($pi[10]) - ord('0')) {
            return false;
        }

        return true;
    }
}
