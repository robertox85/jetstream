<?php

namespace App\Filament\Resources\ScadenzaResource\Pages;

use App\Filament\Resources\ScadenzaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScadenzas extends ListRecords
{
    protected static string $resource = ScadenzaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
