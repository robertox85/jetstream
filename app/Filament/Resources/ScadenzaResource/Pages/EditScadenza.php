<?php

namespace App\Filament\Resources\ScadenzaResource\Pages;

use App\Filament\Resources\ScadenzaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScadenza extends EditRecord
{
    protected static string $resource = ScadenzaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
