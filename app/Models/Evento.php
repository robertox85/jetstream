<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Evento extends Model
{
    use HasFactory, SoftDeletes;

    const TIPO_SCADENZA = 'scadenza';
    const TIPO_UDIENZA = 'udienza';

    const TIPO_APPUNTAMENTO = 'appuntamento';

    protected $table = 'eventi';

    protected $fillable = [
        'pratica_id',
        'user_id',
        'assigned_to',
        'tipo',
        'data_ora',
        'motivo',
        'stato',
        'luogo',
        'reminder_at',
        'email_notification',
        'browser_notification',
    ];

    protected $casts = [
        'data_ora' => 'datetime',
        'reminder_at' => 'array',
        'email_notification' => 'boolean',
        'browser_notification' => 'boolean',
    ];

    public function pratica()
    {
        return $this->belongsTo(Pratica::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub

        static::creating(function ($model) {


            if (!$model->user_id) {
                $model->user_id = auth()->id();
            }

            if (!$model->data_ora) {
                $data = $model->data ?? now()->format('Y-m-d');
                $ora = $model->ora ?? now()->format('H:i');
                $model->data_ora = Carbon::parse($data . ' ' . $ora);
            }
        });
    }
}