<?php

namespace App\Filament\Imports;

use App\Models\Anagrafica;
use App\Models\Assistito;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class AssistitoImporter extends Importer
{
    protected static ?string $model = Anagrafica::class;


    public static function getColumns(): array
    {
        return [
            ImportColumn::make('type')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('tipo_utente')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('denominazione'),
            ImportColumn::make('nome'),
            ImportColumn::make('cognome'),
            ImportColumn::make('indirizzo'),
            ImportColumn::make('codice_postale'),
            ImportColumn::make('citta'),
            ImportColumn::make('provincia'),
            ImportColumn::make('telefono'),
            ImportColumn::make('cellulare'),
            ImportColumn::make('email')
                ->rules(['email']),
            ImportColumn::make('pec'),
            ImportColumn::make('codice_fiscale'),
            ImportColumn::make('partita_iva'),
            ImportColumn::make('codice_univoco_destinatario'),
            ImportColumn::make('nota'),
        ];
    }

    public function resolveRecord(): ?Anagrafica
    {
        // return Assistito::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Anagrafica();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your assistito import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}