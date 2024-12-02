<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;

class GoogleCalendarController extends Controller
{
    private GoogleCalendarService $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function connect()
    {
        return redirect($this->calendarService->getAuthUrl());
    }

    public function callback(Request $request)
    {
        if ($request->has('code')) {
            $this->calendarService->handleCallback($request->code);
            return redirect()->to('admin/calendario')
                ->with('success', 'Google Calendar collegato con successo');
        }
        return redirect()->route('calendar.index')
            ->with('error', 'Errore nella connessione con Google Calendar');
    }
}