<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaDocumento extends Model
{
    /** @use HasFactory<\Database\Factories\CategoriaDocumentoFactory> */
    use HasFactory;

    protected $table = 'categorie_documenti';

    protected $fillable = [
        'nome',
        'descrizione'
    ];

    // RELAZIONI

    public function documenti()
    {
        return $this->hasMany(Documento::class, 'categoria_id');
    }
}
