<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EventoResource;
use App\Filament\Resources\PraticaResource;
use App\Models\Evento;
use App\Services\GoogleCalendarService;
use App\Traits\HasEventoForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CalendarioWidget extends FullCalendarWidget
{
    use HasEventoForm;

    protected $selectedDate = null;

    public GoogleCalendarService $googleCalendar;

    public string|null|\Illuminate\Database\Eloquent\Model $model = Evento::class;

    public function config(): array
    {

        return [
            'initialView' => 'dayGridMonth',
            'locale' => 'it',
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'dayGridMonth,timeGridWeek,timeGridDay googleConnect',  // Aggiunto googleConnect
                'center' => 'title',
                'right' => 'prev,next today',
            ],
            'editable' => true,
            'selectable' => true,
            'dayMaxEvents' => true,
            'customButtons' => [
                'googleConnect' => [
                    'text' => 'Google',
                    'click' => new \stdClass(), // Questo evita l'errore della funzione
                ],
            ],
        ];
    }

    public function eventDidMount(): string
    {
        $route = route('google.connect');
        return <<<JS
        function({ event, el }) {
            // Gestione normale degli eventi
            el.setAttribute("x-tooltip", "tooltip");
            el.setAttribute("x-data", "{ tooltip: '"+event.title+"' }");
            
            // Aggiungi il click handler per il pulsante Google
            document.querySelector('.fc-googleConnect-button').onclick = function() {
                window.location.href = '$route';
            };
        }
    JS;
    }

    public function fetchEvents(array $info): array
    {
        $colori = [
            'udienza' => '#007bff',
            'scadenza' => '#dc3545',
            'appuntamento' => '#28a745',
        ];

        // get eventi with user_id or assigned_to = user_id. If admin or superadmin or segreteria, get all events
        if (auth()->user()->hasRole('Amministratore') || auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('Segreteria')) {
            $eventi = Evento::all();
        } else {
            $eventi = Evento::where('user_id', auth()->id())
                ->orWhere('assigned_to', auth()->id())
                ->get();
        }

        $eventi = $eventi->map(function ($evento) use ($colori) {
            $pratica = $evento->pratica;

            return [
                'id' => $evento->id,
                'title' => $evento->tipo . ' - ' . $pratica->numero_pratica . ' - ' . $evento->luogo,
                'start' => $evento->data_ora,
                'end' => $evento->data_ora,
                'backgroundColor' => $colori[$evento->tipo],
                'extendedProps' => [
                    'evento_id' => $evento->id,
                ],
            ];
        });

        return $eventi->toArray();
    }

    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {

        $this->selectedDate = Carbon::parse($start);

        $this->mountAction('create', [
            'data' => $this->selectedDate->format('Y-m-d'),
            'ora' => $this->selectedDate->format('H:i'),
        ]);

        $this->mountAction('edit', [
            'data' => $this->selectedDate->format('Y-m-d'),
            'ora' => $this->selectedDate->format('H:i'),
        ]);



    }


    public function getFormSchema(): array
    {
        $defaultData = [
            'data' => $this->selectedDate ? $this->selectedDate->format('Y-m-d') : null,
            'ora' => $this->selectedDate ? $this->selectedDate->format('H:i') : null,
        ];

        return static::getEventoForm('appuntamento', $defaultData);


    }

    // Gestisce il drag & drop dell'evento
    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        $evento = Evento::find($event['id']);
        if ($evento) {

            $evento->update([
                'data_ora' => $event['start'],
            ]);
        }

        return false;
    }

}