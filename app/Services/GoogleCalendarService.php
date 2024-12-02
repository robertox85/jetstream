<?php

namespace App\Services;

use Google\Service\Calendar\Event;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class GoogleCalendarService
{
    private Google_Client $client;

    public function __construct()
    {
        $this->client = new Google_Client();
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

    public function isConnected()
    {
        return Session::has('google_token');
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

    public function createEvent($evento): Event
    {
        $service = new Google_Service_Calendar($this->client);

        $attendees = [];
        if ($evento->assignedTo) {
            $attendees[] = ['email' => $evento->assignedTo->email];
        }

        $googleEvent = new Google_Service_Calendar_Event([
            'summary' => "{$evento->tipo} - {$evento->pratica->numero_pratica}",
            'description' => $evento->motivo,
            'location' => $evento->luogo,
            'start' => [
                'dateTime' => $evento->data_ora->format('c'),
                'timeZone' => 'Europe/Rome',
            ],
            'end' => [
                'dateTime' => $evento->data_ora->addHour()->format('c'),
                'timeZone' => 'Europe/Rome',
            ],
            'attendees' => $attendees,
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 24 * 60],
                    ['method' => 'popup', 'minutes' => 30],
                ],
            ],
        ]);

        return $service->events->insert('primary', $googleEvent, [
            'sendUpdates' => 'all'
        ]);
    }
}