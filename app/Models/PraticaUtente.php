<?php

namespace App\Models;

use App\Models\Pratica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PraticaUtente extends Model
{
    use HasFactory;

    protected $table = 'pratica_utente';

    protected $fillable = [
        'pratica_id',
        'utente_id',
        'ruolo',
    ];

    public function pratica()
    {
        return $this->belongsTo(Pratica::class);
    }

    public function utente()
    {
        return $this->belongsTo(User::class);
    }
}
