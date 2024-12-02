<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Appuntamento extends Evento
{
    /** @use HasFactory<\Database\Factories\ScadenzaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'eventi';


}
