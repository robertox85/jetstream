<?php

namespace App\Filament\Resources\ContabilitaResource\Pages;

use App\Filament\Resources\ContabilitaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContabilitas extends ListRecords
{
    protected static string $resource = ContabilitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
