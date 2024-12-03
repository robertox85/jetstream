<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

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

    public function disconnect()
    {
        Session::forget('google_token');
        return redirect()->back()->with('success', 'Disconnesso da Google Calendar');
    }

    public function callback(Request $request)
    {
        if ($request->has('code')) {
            $this->calendarService->handleCallback($request->code);
            return redirect()->to('admin/calendario')
                ->with('success', 'Google Calendar collegato con successo');
        }
        return redirect()->to('admin/calendario')
            ->with('error', 'Errore nella connessione con Google Calendar');
    }
}