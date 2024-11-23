<?php

namespace App\Filament\Imports;

use App\Models\Nota;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class NotaImporter extends Importer
{
    protected static ?string $model = Nota::class;

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
            ImportColumn::make('last_edited_by')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('oggetto')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('nota')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('tipologia')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('visibilita')
                ->requiredMapping()
                ->rules(['required']),
        ];
    }

    public function resolveRecord(): ?Nota
    {
        // return Nota::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Nota();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your nota import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
