<?php

namespace App\Filament\Exports;

use App\Models\Anagrafica;
use App\Models\Assistito;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AssistitoExporter extends Exporter
{
    protected static ?string $model = Anagrafica::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('type'),
            ExportColumn::make('tipo_utente'),
            ExportColumn::make('denominazione'),
            ExportColumn::make('nome'),
            ExportColumn::make('cognome'),
            ExportColumn::make('indirizzo'),
            ExportColumn::make('codice_postale'),
            ExportColumn::make('citta'),
            ExportColumn::make('provincia'),
            ExportColumn::make('telefono'),
            ExportColumn::make('cellulare'),
            ExportColumn::make('email'),
            ExportColumn::make('pec'),
            ExportColumn::make('codice_fiscale'),
            ExportColumn::make('partita_iva'),
            ExportColumn::make('codice_univoco_destinatario'),
            ExportColumn::make('nota'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your assistito export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
