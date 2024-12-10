<?php

namespace App\Filament\Imports;

use App\Models\Udienza;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class UdienzaImporter extends Importer
{
    protected static ?string $model = Udienza::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('pratica_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('user_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),

            ImportColumn::make('data_ora')
                ->requiredMapping()
                ->rules(['required', 'datetime']),
            ImportColumn::make('motivo')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('luogo'),
            ImportColumn::make('stato')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('reminder_at'),
            ImportColumn::make('email_notification')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'email', 'boolean']),
            ImportColumn::make('browser_notification')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): ?Udienza
    {
        // return Udienza::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Udienza();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your udienza import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
