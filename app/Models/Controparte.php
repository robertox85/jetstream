<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
class Controparte extends Anagrafica
{

    protected static function boot()
    {
        parent::boot();

        // Aggiungi global scope
        static::addGlobalScope('controparte', function (Builder $builder) {
            $builder->where('type', self::TYPE_CONTROPARTE);
        });

        static::creating(function ($model) {
            $model->type = self::TYPE_CONTROPARTE;
        });
    }

    // return only type controparte from anagrafiche
    public function scopeControparti($query): Builder
    {
        return $query->where('type', self::TYPE_CONTROPARTE);
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
            ->wherePivot('tipo_relazione', 'controparte');
    }
}