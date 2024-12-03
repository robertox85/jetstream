<?php

namespace App\Observers;

use App\Models\Evento;
use App\Services\GoogleCalendarService;
use Filament\Notifications\Notification;
use Google\Exception;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\Log;
use Google\Client;
use Google\Service\Calendar;


class EventoObserver
{


    /**
     * @throws Exception
     * @throws Google\Service\Exception
     */
    public function createGoogleCalendarEvent(Evento $evento): void
    {
       try {
           $googleCalendar = app(GoogleCalendarService::class);

           if (!$googleCalendar->isConnected()) {
               Notification::make()
                   ->danger()
                   ->title('Google Calendar non collegato')
                   ->send();
           }

           $createdEvent = $googleCalendar->createEvent($evento);

           $evento->update(['google_event_id' => $createdEvent->id]);

           // Usa le notifiche di Filament invece del redirect
           Notification::make()
               ->success()
               ->title('Evento creato su Google Calendar')
               ->send();
       } catch (Exception $e) {
           Log::error($e->getMessage());
           Notification::make()
               ->danger()
               ->title('Errore nella creazione dell\'evento su Google Calendar')
               ->send();
       }
    }

    /**
     * Handle the Evento "created" event.
     * @throws Exception
     */
    public function created(Evento $evento): void
    {
        $this->createGoogleCalendarEvent($evento);
    }

    /**
     * Handle the Evento "updated" event.
     */
    public function updated(Evento $evento): void
    {
        //
    }

    /**
     * Handle the Evento "deleted" event.
     */
    public function deleted(Evento $evento): void
    {
        //
    }

    /**
     * Handle the Evento "restored" event.
     */
    public function restored(Evento $evento): void
    {
        //
    }

    /**
     * Handle the Evento "force deleted" event.
     */
    public function forceDeleted(Evento $evento): void
    {
        //
    }
}
