<?php

namespace App\Services;

use App\Models\Evento;
use Google\Exception;
use Google\Service\Books\Notification;
use Google\Service\Calendar\Event;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventAttendee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Mockery\Matcher\Not;

class GoogleCalendarService
{
    private Google_Client $client;
    private Google_Service_Calendar $service;

    public function __construct()
    {
        $this->client = new Google_Client();

        $this->service = new Google_Service_Calendar($this->client);


        if (app()->environment('local') && config('services.google.test_token')) {

            $this->client->setAccessToken([
                'access_token' => config('services.google.test_token'),
                'expires_in' => 3600,
                'created' => time(),
            ]);
        } else {

            $this->client->setClientId(config('services.google.client_id'));
            $this->client->setClientSecret(config('services.google.client_secret'));
            $this->client->setRedirectUri(config('services.google.redirect'));
            $this->client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);

            if (Session::has('google_token')) {
                $token = Session::get('google_token');
                Log::info('Token: ' . json_encode($token));
                $this->client->setAccessToken(Session::get('google_token'));
            }
        }
    }

    public function isConnected()
    {
        // Considera anche il token di test
        if (app()->environment('local') && config('services.google.test_token')) {
            Log::info('Token di test');
            return true;
        }

        Log::info('Token: ' . json_encode(Session::get('google_token')));

        return Session::has('google_token') && $this->client->getAccessToken();
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function handleCallback(string $code): void
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        Session::put('google_token', $token);
    }

    public function createEvent($evento): Event|null
    {
        try {
            $googleEvent = new Google_Service_Calendar_Event($this->formatEventData($evento));

            $googleEvent = $this->service->events->insert('primary', $googleEvent, [
                'sendUpdates' => 'all',
            ]);

            $evento->update([
                'google_event_id' => $googleEvent->id,
                'google_event_link' => $googleEvent->htmlLink,
            ]);

            return $googleEvent;

        } catch (Exception $e) {
            Log::error('Errore durante la creazione dell\'evento su Google Calendar: ' . $e->getMessage());
            // throw new \Exception('Impossibile creare l\'evento su Google Calendar.');
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Errore durante la creazione dell\'evento su Google Calendar')
                ->send();

            return null;
        }
    }

    // calendarList
    public function calendarList(): array
    {
        try {
            $calendarList = $this->service->calendarList->listCalendarList();
            return $calendarList->getItems();
        } catch (Exception $e) {
            Log::error('Errore durante il recupero della lista dei calendari su Google Calendar: ' . $e->getMessage());
            // throw new \Exception('Impossibile recuperare la lista dei calendari su Google Calendar.');
            return [];
        }
    }

    public function deleteEvent($googleEventId): void
    {
        try {
            $this->service->events->delete('primary', $googleEventId);

            $evento = Evento::where('google_event_id', $googleEventId)->first();
            if (!$evento) {
                return;
            }
            $evento->update([
                'google_event_id' => null,
                'google_event_link' => null,
            ]);

        } catch (Exception $e) {
            Log::error('Errore durante l\'eliminazione dell\'evento su Google Calendar: ' . $e->getMessage());
            // throw new \Exception('Impossibile eliminare l\'evento su Google Calendar.');
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Errore durante l\'eliminazione dell\'evento su Google Calendar')
                ->send();

            // elimina l'evento anche se non è possibile eliminare l'evento su Google Calendar
            $evento = Evento::where('google_event_id', $googleEventId)->first();
            if (!$evento) {
                return;
            }
            $evento->update([
                'google_event_id' => null,
                'google_event_link' => null,
            ]);
        }
    }

    public function updateEvent(Evento $evento): void
    {
        $googleEventId = $evento->google_event_id;
        if (!$googleEventId) {
            return;
        }
        $googleEvent = new Google_Service_Calendar_Event($this->formatEventData($evento));

        $this->service->events->update('primary', $googleEventId, $googleEvent, [
            'sendUpdates' => 'all'
        ]);
    }

    public function downloadEvent($googleEventId): Google_Service_Calendar_Event|null
    {
        try {
            return $this->service->events->get('primary', $googleEventId);
        } catch (Exception $e) {
            Log::error('Errore durante il recupero dell\'evento su Google Calendar: ' . $e->getMessage());
           // throw new \Exception('Impossibile recuperare l\'evento su Google Calendar.');
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Errore durante il recupero dell\'evento su Google Calendar')
                ->send();

            return null;
        }
    }

    private function formatAttendees($invitati): array
    {
        $attendees = [];

        foreach ($invitati as $invitato) {
            $attendees[] = [
                'email' => $invitato->email,
                'displayName' => $invitato->name,
                'responseStatus' => 'needsAction',
            ];
        }

        return $attendees;
    }

    protected function formatEventData(Evento $evento): array
    {
        $attendees = [];

        if ($evento->invitati) {
            $attendees = $this->formatAttendees($evento->invitati);
        }

        $summary = $evento->tipo;

        if ($evento->pratica) {
            $summary = "{$evento->tipo} - {$evento->pratica->numero_pratica}";
        }

        // Convertiamo esplicitamente la data dal database (UTC) a Europe/Rome
        $startDate = Carbon::parse($evento->data_ora)
            ->tz('Europe/Rome');

        $data = [
            'summary' => $summary,
            'description' => $evento->motivo,
            'location' => $evento->luogo,
            'start' => [
                'dateTime' => $startDate->format('Y-m-d\TH:i:s'),  // Formato YYYY-MM-DDThh:mm:ss
                'timeZone' => 'UTC',  // Usiamo UTC dato che la data è già in UTC
            ],
            'end' => [
                'dateTime' => $startDate->copy()->addHour()->format('Y-m-d\TH:i:s'),
                'timeZone' => 'UTC',  // Usiamo UTC dato che la data è già in UTC
            ],
            'attendees' => $attendees,
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 24 * 60], // 24 ore prima
                    ['method' => 'popup', 'minutes' => 30], // 30 minuti prima
                ],
            ],
        ];

        return $data;
    }

    // updateEventWithGuests
    public function updateEventWithGuests(Evento $evento, $invitati): void
    {
        if (!$evento->google_event_id) {
            throw new \Exception('L\'evento su Google Calendar non esiste.');
        }

        $attendees = $this->formatAttendees($invitati);

        try {
            // Recupera l'evento su Google Calendar
            $googleEvent = $this->service->events->get('primary', $evento->google_event_id);

            // Aggiorna gli invitati
            $googleEvent->attendees = $attendees;

            // Aggiorna l'evento su Google Calendar
            $this->service->events->update('primary', $evento->google_event_id, $googleEvent, [
                'sendUpdates' => 'all', // Invia notifiche agli invitati
            ]);

        } catch (Exception $e) {
            Log::error('Errore durante l\'aggiornamento degli invitati su Google Calendar: ' . $e->getMessage());
            // throw new \Exception('Impossibile aggiornare gli invitati su Google Calendar.');
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Errore durante l\'aggiornamento degli invitati su Google Calendar')
                ->send();
        }
    }


    public function disconnect(): void
    {
        Session::forget('google_token');
    }

    // link to google calendar
    // route('google.connect')
    public function connect()
    {
        return redirect($this->getAuthUrl());
    }

    // get attendeeStatus
    public function getAttendeeStatus(Evento $evento, $email): string
    {
        if (!$evento->google_event_id) {
            return 'needsAction';
        }

        try {
            // Recupera l'evento su Google Calendar
            $googleEvent = $this->service->events->get('primary', $evento->google_event_id);
            Log::info('Google Event: ' . json_encode($googleEvent));
            // Cerca l'invitato
            foreach ($googleEvent->attendees as $attendee) {
                if ($attendee->email === $email) {
                    return $attendee->responseStatus;
                }
            }

            return 'needsAction';

        } catch (Exception $e) {
            Log::error('Errore durante il recupero dello stato dell\'invitato su Google Calendar: ' . $e->getMessage());
            // throw new \Exception('Impossibile recuperare lo stato dell\'invitato su Google Calendar.');
            return 'needsAction';
        }
    }


}