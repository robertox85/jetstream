<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Udienza extends Model
{
    /** @use HasFactory<\Database\Factories\UdienzaFactory> */
    use HasFactory;


    protected $table = 'udienze';

    // Definizione degli stati come costanti
    const STATO_DA_INIZIARE = 'da_iniziare';
    const STATO_IN_CORSO = 'in_corso';
    const STATO_COMPLETATA = 'completata';
    const STATO_ANNULLATA = 'annullata';

    // Mass assignment protection
    protected $fillable = [
        'pratica_id',
        'user_id',
        'assigned_to',
        'data_ora',
        'motivo',
        'luogo',
        'stato',
        'reminder_at',
        'email_notification',
        'browser_notification'
    ];

    // Cast automatici
    protected $casts = [
        'data_ora' => 'datetime',
        'reminder_at' => 'array',
        'email_notification' => 'boolean',
        'browser_notification' => 'boolean',
        'deleted_at' => 'datetime',
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

    public function avvocatoAssegnato()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // SCOPE QUERY

    // Udienze future
    public function scopeFuture($query)
    {
        return $query->where('data_ora', '>', now())
            ->where('stato', '!=', self::STATO_ANNULLATA);
    }

    // Udienze della settimana
    public function scopeDellaSettimana($query)
    {
        return $query->whereBetween('data_ora', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // METODI DI UTILITÀ

    // Verifica se l'udienza è imminente
    public function isImminente($giorni = 7)
    {
        return $this->data_ora->between(now(), now()->addDays($giorni));
    }

    // Imposta i reminder
    public function setReminders(array $timestamps)
    {
        $this->reminder_at = collect($timestamps)
            ->map(function($timestamp) {
                return Carbon::parse($timestamp)->format('Y-m-d H:i:s');
            })
            ->toArray();

        $this->save();
    }

    // Rinvia l'udienza
    public function rinvia($nuovaData, $motivo = null)
    {
        // Salva i dati dell'udienza originale
        $this->storiaUdienze()->create([
            'data_originale' => $this->data_ora,
            'motivo_rinvio' => $motivo
        ]);

        $this->update([
            'data_ora' => $nuovaData,
            'stato' => self::STATO_DA_INIZIARE
        ]);
    }
}
