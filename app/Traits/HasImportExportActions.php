<?php

namespace App\Traits;

use App\Filament\Exports\NotaExporter;
use App\Filament\Exports\PraticaExporter;
use Filament\Actions;
use Filament\Tables\Actions\Action;

trait HasImportExportActions {


    public function getCustomHeaderActions(): Actions\ActionGroup
    {

        // if not isset importer and exporter
        if (!isset($this->importer) || !isset($this->exporter)) {
            return Actions\ActionGroup::make([]);
        }

        return Actions\ActionGroup::make([

            Actions\ImportAction::make()
                ->importer($this->importer)
                ->label('Importa'),

            Actions\ExportAction::make()
                ->label('Esporta')
                ->exporter($this->exporter)

        // ])->visible(fn () => auth()->user()->hasRole('Amministratore') || auth()->user()->hasRole('super_admin'));
        ])->visible(fn () =>  auth()->user()->hasRole('super_admin'));
    }
}