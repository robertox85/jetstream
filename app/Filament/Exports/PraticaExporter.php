<?php

namespace App\Filament\Exports;

use App\Models\Pratica;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PraticaExporter extends Exporter
{
    protected static ?string $model = Pratica::class;



    // Export also the soft deleted records
    protected static bool $withTrashed = true;


    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('numero_pratica'),
            ExportColumn::make('nome'),
            ExportColumn::make('tipologia'),
            ExportColumn::make('competenza'),
            ExportColumn::make('ruolo_generale'),
            ExportColumn::make('giudice'),
            ExportColumn::make('stato'),
            ExportColumn::make('altri_riferimenti'),
            ExportColumn::make('priority'),
            ExportColumn::make('data_apertura'),
            ExportColumn::make('team_id'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
            ExportColumn::make('contabilita'),
            ExportColumn::make('lavorazione'),

            // Relazioni anagrafiche
            ExportColumn::make('clienti')
                ->label('Clienti')
                ->state(function (Pratica $record): string {
                    return $record->anagrafiche()
                        ->wherePivot('tipo_relazione', 'cliente')
                        ->pluck('denominazione')
                        ->join(', ');
                }),
            ExportColumn::make('controparti')
                ->label('Controparti')
                ->state(function (Pratica $record): string {
                    return $record->anagrafiche()
                        ->wherePivot('tipo_relazione', 'controparte')
                        ->pluck('denominazione')
                        ->join(', ');
                }),
            // Documenti count
            ExportColumn::make('documenti_count')
                ->label('Numero Documenti')
                ->state(function (Pratica $record): int {
                    return $record->documenti_count ?? 0;
                }),
            // Udienze future
            ExportColumn::make('prossima_udienza')
                ->label('Prossima Udienza')
                ->state(function (Pratica $record): ?string {
                    return $record->udienze()
                        ->where('data_ora', '>=', now())
                        ->orderBy('data_ora')
                        ->first()?->data_ora?->format('d/m/Y H:i');
                }),
        ];
    }


    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your pratica export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
