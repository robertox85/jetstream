<?php

namespace App\Filament\Resources\ControparteResource\Pages;

use App\Filament\Resources\ControparteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContropartes extends ListRecords
{
    protected static string $resource = ControparteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
