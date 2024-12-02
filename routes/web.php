<?php

use App\Http\Controllers\GoogleCalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});


Route::get('admin/google/connect', [GoogleCalendarController::class, 'connect'])->name('google.connect');
Route::get('admin/google/callback', [GoogleCalendarController::class, 'callback'])->name('google.callback');