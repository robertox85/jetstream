<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnagraficaPratica extends Model
{
    /** @use HasFactory<\Database\Factories\AnagraficaPraticaFactory> */
    use HasFactory;

    protected $table = 'anagrafica_pratica';

    protected $fillable = [
        'pratica_id',
        'anagrafica_id',
        'tipo_relazione'
    ];

    // Abilita soft delete
    use SoftDeletes;

    public function pratica()
    {
        return $this->belongsTo(Pratica::class);
    }

    public function anagrafica()
    {
        return $this->belongsTo(Anagrafica::class);
    }

    // Verifica se è controparte
    public function isControparte()
    {
        return $this->tipo_relazione === Anagrafica::TYPE_CONTROPARTE;
    }

    // Verifica se è assistito
    public function isAssistito()
    {
        return $this->tipo_relazione === Anagrafica::TYPE_ASSISTITO;
    }
}
