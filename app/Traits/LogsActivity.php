<?php

namespace App\Traits;

// Trait da utilizzare nei model che si vogliono loggare
use App\Models\ActivityLog;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        // Log alla creazione
        static::created(function ($model) {
            $model->logActivity(ActivityLog::ACTION_CREATED);
        });

        // Log alla modifica
        static::updated(function ($model) {
            $model->logActivity(ActivityLog::ACTION_UPDATED);
        });

        // Log alla cancellazione
        static::deleted(function ($model) {
            $model->logActivity(ActivityLog::ACTION_DELETED);
        });

        // Log al ripristino
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->logActivity(ActivityLog::ACTION_RESTORED);
            });
        }
    }

    // Relazione con i log
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    // Crea un nuovo log
    protected function logActivity($action)
    {
        // Non logga se non c'è un utente autenticato
        if (!auth()->check()) {
            return;
        }

        $oldValues = null;
        $newValues = null;

        if ($action === ActivityLog::ACTION_UPDATED) {
            $oldValues = array_intersect_key(
                $this->getOriginal(),
                $this->getDirty()
            );
            $newValues = $this->getDirty();
        } elseif ($action === ActivityLog::ACTION_CREATED) {
            $newValues = $this->getAttributes();
        }

        $this->activityLogs()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues
        ]);
    }

    // Metodi di utilità per i log

    // Recupera la cronologia delle modifiche
    public function getActivityHistory()
    {
        return $this->activityLogs()
            ->with('user')
            ->latest()
            ->get();
    }

    // Recupera l'ultimo utente che ha modificato
    public function getLastModifiedByAttribute()
    {
        return $this->activityLogs()
            ->where('action', ActivityLog::ACTION_UPDATED)
            ->latest()
            ->first()
            ->user ?? null;
    }
}