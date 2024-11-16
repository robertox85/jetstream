<?php

namespace App\Filament\Resources\PraticaResource\Pages;

use App\Filament\Resources\PraticaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPraticas extends ListRecords
{
    protected static string $resource = PraticaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
