<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityLogFactory> */
    use HasFactory;

    // Costanti per i tipi di azione
    const ACTION_CREATED = 'create';
    const ACTION_UPDATED = 'update';
    const ACTION_DELETED = 'delete';
    const ACTION_RESTORED = 'restore';

    protected $fillable = [
        'user_id',
        'action',
        'old_values',
        'new_values'
    ];

    // Cast automatici
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    // RELAZIONI

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loggable()
    {
        return $this->morphTo();
    }

    // METODI DI UTILITÀ

    // Restituisce le modifiche in formato leggibile
    public function getChangesAttribute()
    {
        $changes = [];

        if ($this->action === self::ACTION_CREATED) {
            return 'Record creato';
        }

        if ($this->action === self::ACTION_DELETED) {
            return 'Record eliminato';
        }

        if ($this->action === self::ACTION_RESTORED) {
            return 'Record ripristinato';
        }

        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'from' => $oldValue,
                    'to' => $newValue
                ];
            }
        }

        return $changes;
    }
}
