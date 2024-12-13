<?php

namespace App\Filament\Widgets;

use App\Models\Evento;
use App\Models\EventoInvitati;
use App\Services\GoogleCalendarService;
use App\Traits\HasEventoForm;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarioWidget extends FullCalendarWidget
{
    use HasEventoForm;

    protected bool $notifica = false;
    protected ?string $selectedDate = null;
    protected ?string $selectedTime = null;
    protected ?int $selectedEventId = null;
    protected bool $isConnected;
    protected GoogleCalendarService $googleCalendarService;

    public string|null|Model $model = Evento::class;

    public function __construct()
    {
        $this->googleCalendarService = app(GoogleCalendarService::class);
        $this->isConnected = $this->googleCalendarService->isConnected();

        // if user is 'Segreteria' or 'Amministratore' show notification

        if ($this->isAdminUser()) {
            if (!$this->isConnected) {
                Filament::registerRenderHook(
                    'panels::body.start',
                    fn(): string => view('filament.components.google-calendar-banner', [
                        'connectRoute' => route('google.connect')
                    ])->render(),
                );
            }
        }
    }

    public function config(): array
    {

        return [
            'initialView' => 'dayGridMonth',
            'locale' => 'it',
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => "dayGridMonth,timeGridWeek,timeGridDay listWeek",
                'center' => 'title',
                'right' => 'today prev,next',
            ],
            'editable' => true,
            'selectable' => true,
            'dayMaxEvents' => true,
            'customButtons' => $this->getCustomButtons(),
        ];
    }

    protected function getCustomButtons(): array
    {
        return [
            'googleConnect' => [
                'text' => 'Connetti Google',
                'click' => new \stdClass()
            ],
            'googleDisconnect' => [
                'text' => 'Disconnetti Google',
                'click' => new \stdClass(),
            ],
        ];
    }


    protected function headerActions(): array
    {
        if ($this->isAdminUser()) {
            return [
                ...parent::headerActions(),
                $this->getConnectionAction(),
                $this->getDisconnectAction(),
                $this->getSyncAllEventsAction(),
            ];
        }

        return [
            ...parent::headerActions()
        ];
    }

    protected function modalActions(): array
    {
        if ($this->isAdminUser()) {
            return [
                ...parent::modalActions(),
                $this->getViewOnGoogleAction(),
                $this->getSyncEventAction(),
                $this->getDeleteFromGoogleAction(),
            ];
        }
        return [
            ...parent::modalActions()
        ];
    }


    protected function getViewOnGoogleAction(): Action
    {
        return Action::make('viewOnGoogle')
            ->label('Visualizza su Google')
            ->visible(fn(Model $record) => $record->google_event_id)
            ->tooltip('Visualizza questo evento su Google Calendar')
            ->action(fn(Model $record) => $this->viewOnGoogle($record));
    }

    public function viewOnGoogle(Model $evento)
    {
        return redirect()->away($evento->google_event_link);
    }


    protected function getDeleteFromGoogleAction(): Action
    {
        return Action::make('deleteFromGoogle')
            ->label('Elimina da Google')
            ->requiresConfirmation()
            ->visible(fn(Model $record) => $record->google_event_id)
            ->tooltip('Elimina questo evento da Google Calendar')
            ->disabled(fn() => !$this->isConnected)
            ->action(function (Model $record) {
                $this->deleteFromGoogle($record);
                $record->update([
                    'google_event_id' => null,
                    'google_event_link' => null,
                ]);

                $this->refreshRecords();

                Notification::make()
                    ->success()
                    ->title('Evento eliminato da Google Calendar')
                    ->send();
            });
    }

    public function deleteFromGoogle(Model $evento): void
    {
        $this->googleCalendarService->deleteEvent($evento->google_event_id);
    }

    protected function getSyncEventAction(): Action
    {
        return Action::make('syncEvent')
            ->label('Sincronizza su Google')
            ->requiresConfirmation()
            ->visible(fn(Model $record) => !$record->google_event_id)
            ->tooltip('Crea questo evento su Google Calendar')
            ->disabled(fn() => !$this->isConnected)
            ->action(function (Model $record) {
                $this->syncEvent($record);

                $record->update([
                    'google_event_id' => $record->google_event_id,
                    'google_event_link' => $record->google_event_link,
                ]);

                $this->refreshRecords();

                Notification::make()
                    ->success()
                    ->title('Evento sincronizzato con Google Calendar')
                    ->send();
            });
    }

    public function syncEvent(Model $evento): void
    {
        $this->googleCalendarService->createEvent($evento);
    }

    protected function getConnectionAction(): Action
    {
        return Action::make('googleConnect')
            ->label('Connetti Google')
            ->visible(fn() => !$this->isConnected)
            ->tooltip('Connetti il tuo account Google Calendar')
            ->action(fn() => $this->googleCalendarService->connect());
    }

    protected function getDisconnectAction(): Action
    {
        return Action::make('googleDisconnect')
            ->label('Disconnetti Google')
            ->visible(fn() => $this->isConnected)
            ->tooltip('Disconnetti il tuo account Google Calendar')
            ->action(fn() => $this->googleCalendarService->disconnect());
    }

    protected function getSyncAllEventsAction(): Action
    {
        return Action::make('syncAllEvents')
            ->label('Sincronizza tutti gli eventi')
            ->requiresConfirmation()
            ->tooltip('Sincronizza tutti gli eventi con Google Calendar')
            ->visible(fn() => $this->isConnected)
            ->action(function () {

                // Recupera tutti gli eventi non sincronizzati
                $eventi = Evento::where('google_event_id', null)->get();

                // Sincronizza tutti gli eventi con Google Calendar
                $eventi->each(fn($evento) => $this->googleCalendarService->createEvent($evento));

                // Ricarica il widget per visualizzare gli eventi sincronizzati
                Notification::make()
                    ->success()
                    ->title('Eventi sincronizzati con Google Calendar')
                    ->send();

                $this->refreshRecords();
            });
    }

    public function eventDidMount(): string
    {

        return $this->getEventMountJavaScript(
            route('google.connect'),
            route('google.disconnect'),
            $this->isConnected ? 'true' : 'false'  // Convert boolean to string
        );
    }

    protected function getEventMountJavaScript(string $connectRoute, string $disconnectRoute, bool $isConnected): string
    {

        return <<<JS
function({ event, el }) {
    
    // Funzione per formattare la data in italiano
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('it-IT', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Gestione stato invitati con colori
    let invitatsList = '';
    if (event.extendedProps.invitati) {
        event.extendedProps.invitati.forEach(function(invitato) {
            let statusClass = 'text-gray-500';
            let statusIcon = '○';
            let statusText = 'In attesa';
            
            if (invitato.status === 'accepted' || invitato.status === 'yes') {
                statusIcon = '✓';
                statusClass = 'text-green-600';
                statusText = 'Confermato';
            } else if (invitato.status === 'declined' || invitato.status === 'no') {
                statusIcon = '✗';
                statusClass = 'text-red-600';
                statusText = 'Rifiutato';
            } else if (invitato.status === 'tentative' || invitato.status === 'maybe') {
                statusIcon = '?';
                statusClass = 'text-yellow-600';
                statusText = 'Forse';
            }
            
            invitatsList += 
                '<div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">' +
                    '<span class="text-sm text-gray-700">' + invitato.nome + '</span>' +
                    '<span class="' + statusClass + ' text-sm ml-2">' + statusIcon + ' ' + statusText + '</span>' +
                '</div>';
        });
    }

    // Determina il tipo di evento e il suo colore/icona
    let tipoEvento = '';
    let tipoIcon = '';
    switch(event.extendedProps.tipo) {
        case 'udienza':
            tipoEvento = 'Udienza';
            tipoIcon = '⚖️';
            break;
        case 'scadenza':
            tipoEvento = 'Scadenza';
            tipoIcon = '⏰';
            break;
        case 'appuntamento':
            tipoEvento = 'Appuntamento';
            tipoIcon = '📅';
            break;
        default:
            tipoEvento = 'Evento';
            tipoIcon = '📌';
    }
    
    // Costruisce il contenuto del tooltip con tutti i dettagli
    const tooltipContent = 
        '<div class="p-4 max-w-sm bg-white rounded shadow-lg">' +
            '<div class="flex items-center justify-between mb-2">' +
                '<div class="font-bold text-gray-900">' + event.title + '</div>' +
                '<div class="text-sm text-gray-500 flex w-full justify-end">' + tipoIcon + ' ' + tipoEvento + '</div>' +
            '</div>' +
            
            '<div class="text-sm text-gray-600 mb-3">' +
                '<div class="mb-1">' + formatDate(event.start) + '</div>' +
                '<div class="' + (event.extendedProps.isSynced ? 'text-green-600' : 'text-gray-500') + '">' +
                    (event.extendedProps.isSynced ? '✓ Sincronizzato con Google Calendar' : '○ Non sincronizzato') +
                '</div>' +
            '</div>' +
            
            (event.extendedProps.note ? 
                '<div class="mb-3 text-sm text-gray-700 border-l-2 border-gray-200 pl-2">' + 
                    event.extendedProps.note + 
                '</div>' : ''
            ) +
            
            (event.extendedProps.invitati.length > 0 ? 
                '<div class="border-t border-gray-100 pt-2 mt-2">' +
                    '<div class="text-sm font-medium text-gray-700 mb-1">Partecipanti</div>' +
                    invitatsList +
                '</div>' : ''
            ) +
        '</div>';
     // Applica il tooltip con configurazione per HTML
    el.setAttribute("x-tooltip", "tooltip");
    el.setAttribute("x-data", "{ tooltip: { content: '" + tooltipContent + "', allowHTML: true } }");
    
    // Gestione bottoni Google
    setTimeout(function() {
        const isConnected = {$isConnected};  // JavaScript will receive the actual string 'true' or 'false'
        if (isConnected) {
            const disconnectButton = document.querySelector('.fc-googleDisconnect-button');
            console.log('disconnect' + disconnectButton);
            if (disconnectButton) {
                disconnectButton.onclick = function() {
                    window.location.href = '$disconnectRoute';
                }
            }
        } else {
            const connectButton = document.querySelector('.fc-googleConnect-button');
            console.log('connect' + connectButton);
            if (connectButton) {
                connectButton.onclick = function() {
                    window.location.href = '$connectRoute';
                }
            }
        }
    }, 0);
}
JS;
    }

    public function fetchEvents(array $info): array
    {
        $eventi = $this->getFilteredEvents();
        return $this->formatEvents($eventi);
    }

    protected function getFilteredEvents()
    {
        if ($this->isAdminUser()) {
            return Evento::all();
        }

        return Evento::where('user_id', auth()->id())
            ->orWhereHas('invitati', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->get();
    }

    protected function isAdminUser(): bool
    {
        return auth()->user()->hasRole(['Amministratore', 'super_admin', 'Segreteria']);
    }

    protected function formatEvents($eventi): array
    {
        $colori = [
            'udienza' => '#007bff',
            'scadenza' => '#dc3545',
            'appuntamento' => '#28a745',
        ];

        return $eventi->map(function ($evento) use ($colori) {
            return [
                'id' => $evento->id,
                'title' => $this->formatEventTitle($evento),
                'start' => $evento->data_ora,
                'end' => $evento->data_ora,
                'backgroundColor' => $colori[$evento->tipo] ?? '#6c757d',
                'extendedProps' => $this->getEventExtendedProps($evento),
            ];
        })->toArray();
    }

    protected function formatEventTitle($evento): string
    {
        return $evento->pratica
            ? "{$evento->tipo} - {$evento->pratica->numero_pratica}"
            : $evento->tipo;
    }

    protected function getEventExtendedProps($evento): array
    {
        return [
            'evento_id' => $evento->id,
            'isSynced' => (bool)$evento->google_event_id,
            'pratica_id' => $evento->pratica_id,
            'invitati' => $this->formatInvitati($evento),
            'tipo' => $evento->tipo,
        ];
    }

    protected function formatInvitati($evento): array
    {
        return $evento->invitati->map(function ($invitato) use ($evento) {
            return [
                'nome' => $invitato->name,
                'status' => $this->googleCalendarService->getAttendeeStatus($evento, $invitato->email),
            ];
        })->toArray();
    }

    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {
        $dateTime = Carbon::parse($start);
        $this->selectedDate = $dateTime->format('Y-m-d');
        $this->selectedTime = now()->format('H:i:s');

        $this->mountAction('create', [
            'data' => $this->selectedDate,
            'ora' => $this->selectedTime,
            'data_ora' => $dateTime->format('Y-m-d H:i:s')
        ]);
    }

    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        $evento = Evento::find($event['id']);

        if (!$evento) {
            return false;
        }

        $updated = $evento->update([
            'data_ora' => Carbon::parse($event['start'])->format('Y-m-d H:i:s')
        ]);

        if ($updated) {
            // Notifica l'utente del successo
            Notification::make()
                ->success()
                ->title('Evento spostato con successo')
                ->send();

            $this->refreshRecords();
        }

        return $updated;
    }

    public function getFormSchema(): array
    {
        $defaultData = [];

        if ($this->selectedDate && $this->selectedTime) {
            $defaultData = [
                'data' => $this->selectedDate,
                'ora' => $this->selectedTime,
                'data_ora' => "{$this->selectedDate} {$this->selectedTime}",
            ];
        }

        return static::getEventoForm('appuntamento', $defaultData);
    }
}