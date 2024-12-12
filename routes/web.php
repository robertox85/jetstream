<?php

use App\Http\Controllers\GoogleCalendarController;
use App\Http\Middleware\RestrictPraticaFiles;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use UniSharp\LaravelFilemanager\Lfm;

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

Route::group(['prefix' => 'laravel-filemanager/{pratica}', 'middleware' => ['web', 'auth']], function () {
    Lfm::routes();
});

Route::group(['prefix' => 'filemanager/{pratica}', 'middleware' => ['web', 'auth']], function () {
    Lfm::routes();
});



Route::get('admin/google/connect', [GoogleCalendarController::class, 'connect'])->name('google.connect');
Route::get('admin/google/callback', [GoogleCalendarController::class, 'callback'])->name('google.callback');
Route::get('admin/google/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google.disconnect');
