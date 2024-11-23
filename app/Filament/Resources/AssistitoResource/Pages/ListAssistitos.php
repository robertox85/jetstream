<?php

namespace App\Filament\Resources\AssistitoResource\Pages;

use App\Filament\Exports\AssistitoExporter;
use App\Filament\Imports\AssistitoImporter;
use App\Filament\Resources\AssistitoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;


class ListAssistitos extends ListRecords
{
    use \App\Traits\HasImportExportActions;
    protected static string $resource = AssistitoResource::class;

    protected $importer = AssistitoImporter::class;
    protected $exporter = AssistitoExporter::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            $this->getCustomHeaderActions(),
        ];
    }
}
