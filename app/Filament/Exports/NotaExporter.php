<?php

namespace App\Filament\Exports;

use App\Models\Nota;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class NotaExporter extends Exporter
{
    protected static ?string $model = Nota::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('pratica_id'),
            ExportColumn::make('user_id'),
            ExportColumn::make('last_edited_by'),
            ExportColumn::make('oggetto'),
            ExportColumn::make('nota'),
            ExportColumn::make('tipologia'),
            ExportColumn::make('visibilita'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your nota export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
