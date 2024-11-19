<?php

namespace App\Filament\Resources\LavorazioneResource\Pages;

use App\Filament\Resources\LavorazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLavoraziones extends ListRecords
{
    protected static string $resource = LavorazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
