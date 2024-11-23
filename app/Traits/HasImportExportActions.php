<?php

namespace App\Traits;

use App\Filament\Exports\NotaExporter;
use App\Filament\Exports\PraticaExporter;
use Filament\Actions;
use Filament\Tables\Actions\Action;

trait HasImportExportActions {


    public function getCustomHeaderActions(): Actions\ActionGroup
    {
        return Actions\ActionGroup::make([



            Actions\ImportAction::make()
                ->importer($this->importer)
                ->label('Importa'),

            Actions\ExportAction::make()
                ->label('Esporta')
                ->exporter($this->exporter)

        ])->visible(fn () => auth()->user()->hasRole('Amministratore') || auth()->user()->hasRole('super_admin'));
    }
}