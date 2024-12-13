<?php

namespace App\Observers;

use App\Models\Evento;
use App\Services\GoogleCalendarService;
use Filament\Notifications\Notification;
use Google\Exception;
use Illuminate\Support\Facades\Log;


class EventoObserver
{
    private bool $isSyncing = false;

    /**
     * Handle the Evento "created" event.
     * @throws Exception
     */
    public function created(Evento $evento): void
    {
        if ($this->isSyncing) {
            return;
        }

        try {
            $this->isSyncing = true;
            $googleCalendar = app(GoogleCalendarService::class);

            if ($googleCalendar->isConnected()) {
                $googleCalendar->createEvent($evento);

                // Usa le notifiche di Filament invece del redirect
                Notification::make()
                    ->success()
                    ->title('Evento aggiunto a Google Calendar')
                    ->send();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Notification::make()
                ->danger()
                ->title('Errore nell\'aggiornamento dell\'evento su Google Calendar (created) ')
                ->send();
        } finally {
            $this->isSyncing = false;
        }


    }

    /**
     * Handle the Evento "updated" event.
     * @throws Exception
     */
    public function updated(Evento $evento): void
    {
        if ($this->isSyncing) {
            return;
        }

        try {
            $this->isSyncing = true;

            $googleCalendar = app(GoogleCalendarService::class);

            if ($googleCalendar->isConnected()) {
                $googleCalendar->updateEvent($evento);
            }

        } catch (Exception $e) {
            Log::error($e->getMessage());
            Notification::make()
                ->danger()
                ->title('Errore nell\'aggiornamento dell\'evento su Google Calendar (updated) ')
                ->send();
        } finally {
            $this->isSyncing = false;
        }
    }

    /**
     * Handle the Evento "deleted" event.
     */
    public function deleted(Evento $evento): void
    {
        $this->deleteGoogleCalendarEvent($evento);
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

    private function deleteGoogleCalendarEvent(Evento $evento)
    {
        try {
            $googleCalendar = app(GoogleCalendarService::class);

            if (!$googleCalendar->isConnected()) {
                Notification::make()
                    ->danger()
                    ->title('Google Calendar non collegato')
                    ->send();
            }

            $googleEventId = $evento->google_event_id;
            if (!$googleEventId) {
                Notification::make()
                    ->danger()
                    ->title('Evento non trovato su Google Calendar')
                    ->send();
            }

            $googleCalendar->deleteEvent($googleEventId);

            // Usa le notifiche di Filament invece del redirect
            Notification::make()
                ->success()
                ->title('Evento cancellato da Google Calendar')
                ->send();

        } catch (Exception $e) {

            Log::error($e->getMessage());

            Notification::make()
                ->danger()
                ->title('Errore nella cancellazione dell\'evento su Google Calendar')
                ->send();

        }
    }
}
