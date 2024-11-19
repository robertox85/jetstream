<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lavorazione extends Model
{
    protected $table = 'lavorazioni';

    protected $fillable = [
        'pratica_id',
        'user_id',
        'last_edited_by',
        'data_inizio',
        'data_fine',
        'descrizione'
    ];

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

    // Boot del model per gestire gli eventi
    protected static function boot()
    {
        parent::boot();

        // Prima del salvataggio, se è una nuova nota
        static::creating(function ($nota) {

            // Traccia l'utente che ha creato la nota
            if (auth()->check()) {
                $nota->user_id = auth()->id();
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
}
