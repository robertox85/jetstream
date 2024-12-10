<?php

namespace App\Models;

use App\Services\GoogleCalendarService;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Log;

class EventoInvitati extends Pivot
{
    protected $table = 'evento_invitati';

    protected static function boot()
    {
        parent::boot();

        static::created(function ($pivot) {
            $evento = Evento::find($pivot->evento_id);
            // Recupera tutti gli invitati
            $invitati = $evento->invitati()->get();

            // Se l'evento è su Google Calendar, aggiorna gli invitati
            if ($evento->google_event_id) {
                app(GoogleCalendarService::class)->updateEventWithGuests($evento, $invitati);
            }
        });

        static::deleted(function ($pivot) {
            $evento = Evento::find($pivot->evento_id);
            // Recupera tutti gli invitati
            $invitati = $evento->invitati()->get();

            // Se l'evento è su Google Calendar, aggiorna gli invitati
            if ($evento->google_event_id) {
                app(GoogleCalendarService::class)->updateEventWithGuests($evento, $invitati);
            }
        });

        static::updated(function ($pivot) {
            $evento = Evento::find($pivot->evento_id);
            // Recupera tutti gli invitati
            $invitati = $evento->invitati()->get();

            // Se l'evento è su Google Calendar, aggiorna gli invitati
            if ($evento->google_event_id) {
                app(GoogleCalendarService::class)->updateEventWithGuests($evento, $invitati);
            }
        });
    }
}
