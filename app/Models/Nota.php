<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Nota extends Model
{
    /** @use HasFactory<\Database\Factories\NotaFactory> */
    use HasFactory;

    protected $table = 'note';

    // Definizione delle tipologie come costanti
    const TIPO_REGISTRO_CONTABILE = 'registro_contabile';
    const TIPO_ANNOTAZIONI = 'annotazioni';

    // Definizione dei livelli di visibilità come costanti
    const VISIBILITA_PRIVATA = 'privata';
    const VISIBILITA_PUBBLICA = 'pubblica';

    // Mass assignment protection
    protected $fillable = [
        'pratica_id',
        'user_id',
        'last_edited_by',
        'oggetto',
        'nota',
        'tipologia',
        'visibilita'
    ];

    // RELAZIONI

    public function pratica()
    {
        return $this->belongsTo(Pratica::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lastEditor()
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    // SCOPE QUERY

    // Note pubbliche
    public function scopePubbliche($query)
    {
        return $query->where('visibilita', self::VISIBILITA_PUBBLICA);
    }

    // Note private di un utente specifico
    public function scopePrivateDiUtente($query, $userId)
    {
        return $query->where('visibilita', self::VISIBILITA_PRIVATA)
            ->where('user_id', $userId);
    }

    // Note contabili
    public function scopeContabili($query)
    {
        return $query->where('tipologia', self::TIPO_REGISTRO_CONTABILE);
    }

    // METODI DI UTILITÀ

    // Verifica se la nota è modificabile dall'utente
    public function isEditableBy(User $user)
    {
        return $user->id === $this->user_id ||
            ($this->visibilita === self::VISIBILITA_PUBBLICA &&
                $user->hasPermissionTo('edit_notes'));
    }

    // Modifica la nota tracciando l'ultimo editor
    public function updateNota($attributes, User $editor)
    {
        $this->fill($attributes);
        $this->last_edited_by = $editor->id;
        $this->save();
    }

    // Boot del model per gestire gli eventi
    protected static function boot()
    {
        parent::boot();

        // Prima del salvataggio, se è una nuova nota
        static::creating(function ($nota) {
            // Se non è specificata la visibilità, imposta come pubblica
            if (!isset($nota->visibilita)) {
                $nota->visibilita = self::VISIBILITA_PUBBLICA;
            }
        });

        // Prima dell'aggiornamento
        static::updating(function ($nota) {
            // Traccia l'utente che sta modificando
            if (auth()->check()) {
                $nota->last_edited_by = auth()->id();
            }
        });
    }

    // ATTRIBUTI ACCESSORI

    // Restituisce una versione troncata della nota per le preview
    public function getPreviewAttribute()
    {
        return Str::limit($this->nota, 100);
    }

    // Indica se la nota è stata modificata
    public function getIsModifiedAttribute()
    {
        return $this->updated_at->gt($this->created_at);
    }
}
