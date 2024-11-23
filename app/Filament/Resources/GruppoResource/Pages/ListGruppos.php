<?php

namespace App\Filament\Resources\GruppoResource\Pages;

use App\Filament\Exports\ControparteExporter;
use App\Filament\Exports\GruppoExporter;
use App\Filament\Imports\ControparteImporter;
use App\Filament\Imports\GruppoImporter;
use App\Filament\Resources\GruppoResource;
use App\Traits\HasImportExportActions;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGruppos extends ListRecords
{
    protected static string $resource = GruppoResource::class;

    use HasImportExportActions;

    protected $importer = GruppoImporter::class;
    protected $exporter = GruppoExporter::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            $this->getCustomHeaderActions(),
        ];
    }
}
