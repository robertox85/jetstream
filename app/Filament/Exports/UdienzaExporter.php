<?php

namespace App\Filament\Exports;

use App\Models\Udienza;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UdienzaExporter extends Exporter
{
    protected static ?string $model = Udienza::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('pratica_id'),
            ExportColumn::make('user_id'),
            ExportColumn::make('assigned_to'),
            ExportColumn::make('data_ora'),
            ExportColumn::make('motivo'),
            ExportColumn::make('luogo'),
            ExportColumn::make('stato'),
            ExportColumn::make('reminder_at'),
            ExportColumn::make('email_notification'),
            ExportColumn::make('browser_notification'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your udienza export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
