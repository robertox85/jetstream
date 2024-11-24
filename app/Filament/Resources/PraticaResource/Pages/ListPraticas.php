<?php

namespace App\Filament\Resources\PraticaResource\Pages;

use App\Filament\Exports\PraticaExporter;
use App\Filament\Imports\PraticaImporter;
use App\Filament\Resources\PraticaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPraticas extends ListRecords
{
    use \App\Traits\HasImportExportActions;

    protected static string $resource = PraticaResource::class;

    protected $importer = PraticaImporter::class;
    protected $exporter = PraticaExporter::class;

    protected ?string $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            self::getCustomHeaderActions(),

        ];
    }
}
