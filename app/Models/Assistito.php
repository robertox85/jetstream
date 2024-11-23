<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Assistito extends Anagrafica
{

    protected static function boot()
    {
        parent::boot();

        // Aggiungi global scope
        static::addGlobalScope('assistito', function (Builder $builder) {
            $builder->where('type', self::TYPE_ASSISTITO);
        });

        static::creating(function ($model) {
            $model->type = self::TYPE_ASSISTITO;
        });
    }

    // return only type controparte from anagrafiche
    public function scopeAssistiti($query): Builder
    {
        return $query->where('type', self::TYPE_ASSISTITO);
    }

    protected function scopeOrderByNomeCompleto($query, $direction = 'asc')
    {
        return $query->orderByRaw("
        CASE 
            WHEN tipo_utente = ? THEN CONCAT(COALESCE(nome, ''), ' ', COALESCE(cognome, ''))
            ELSE COALESCE(denominazione, '')
        END {$direction}
    ", [Anagrafica::TIPO_PERSONA]);
    }

    public function pratiche(): BelongsToMany
    {
        return $this->belongsToMany(
            Pratica::class,
            'anagrafica_pratica',
            'anagrafica_id',
            'pratica_id'
        )
            ->withPivot('tipo_relazione')
            ->withTimestamps()
            ->wherePivot('tipo_relazione', self::TYPE_ASSISTITO)
            ->withPivotValue('tipo_relazione', self::TYPE_ASSISTITO);
    }



    public function pratica(): BelongsToMany
    {
        return $this->belongsToMany(
            Pratica::class,
            'anagrafica_pratica',
            'anagrafica_id',
            'pratica_id'
        )
            ->withPivot('tipo_relazione')
            ->withTimestamps()
            ->wherePivot('tipo_relazione', self::TYPE_ASSISTITO);
    }
}