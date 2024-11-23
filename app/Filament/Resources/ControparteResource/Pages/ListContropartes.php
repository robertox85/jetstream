<?php

namespace App\Filament\Resources\ControparteResource\Pages;

use App\Filament\Exports\ControparteExporter;
use App\Filament\Imports\AssistitoImporter;
use App\Filament\Imports\ControparteImporter;
use App\Filament\Resources\ControparteResource;
use App\Traits\HasImportExportActions;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContropartes extends ListRecords
{
    use HasImportExportActions;

    protected $importer = ControparteImporter::class;
    protected $exporter = ControparteExporter::class;

    protected static string $resource = ControparteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            $this->getCustomHeaderActions(),
        ];
    }

}
