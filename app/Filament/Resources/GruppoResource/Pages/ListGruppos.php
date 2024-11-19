<?php

namespace App\Filament\Resources\GruppoResource\Pages;

use App\Filament\Resources\GruppoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGruppos extends ListRecords
{
    protected static string $resource = GruppoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
