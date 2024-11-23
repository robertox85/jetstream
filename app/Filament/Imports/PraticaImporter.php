<?php

namespace App\Filament\Imports;

use App\Models\Pratica;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PraticaImporter extends Importer
{
    protected static ?string $model = Pratica::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('numero_pratica'),
            ImportColumn::make('nome')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('tipologia'),
            ImportColumn::make('competenza'),
            ImportColumn::make('ruolo_generale'),
            ImportColumn::make('giudice'),
            ImportColumn::make('stato'),
            ImportColumn::make('altri_riferimenti'),
            ImportColumn::make('priority'),
            ImportColumn::make('data_apertura'),
            ImportColumn::make('team_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('contabilita'),
            ImportColumn::make('lavorazione'),
        ];
    }

    public function resolveRecord(): ?Pratica
    {
        // return Pratica::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Pratica();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your pratica import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
