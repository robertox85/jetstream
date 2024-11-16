<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Scadenza extends Model
{
    /** @use HasFactory<\Database\Factories\ScadenzaFactory> */
    use HasFactory;

    protected $table = 'scadenze';

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

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // SCOPE QUERY

    // Scadenze imminenti
    public function scopeImminenti($query, $giorni = 7)
    {
        return $query->whereBetween('data_ora', [now(), now()->addDays($giorni)]);
    }

    // Scadenze in ritardo
    public function scopeInRitardo($query)
    {
        return $query->where('data_ora', '<', now())
            ->whereNotIn('stato', [self::STATO_COMPLETATA, self::STATO_ANNULLATA]);
    }

    // METODI DI UTILITÀ

    // Verifica se la scadenza è imminente
    public function isImminente($giorni = 7)
    {
        return $this->data_ora->between(now(), now()->addDays($giorni));
    }

    // Verifica se la scadenza è in ritardo
    public function isInRitardo()
    {
        return $this->data_ora->isPast() &&
            !in_array($this->stato, [self::STATO_COMPLETATA, self::STATO_ANNULLATA]);
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
}
