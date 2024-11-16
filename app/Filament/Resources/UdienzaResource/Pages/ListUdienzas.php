<?php

namespace App\Filament\Resources\UdienzaResource\Pages;

use App\Filament\Resources\UdienzaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUdienzas extends ListRecords
{
    protected static string $resource = UdienzaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
