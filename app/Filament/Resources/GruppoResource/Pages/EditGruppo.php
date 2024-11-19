<?php

namespace App\Filament\Resources\GruppoResource\Pages;

use App\Filament\Resources\GruppoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGruppo extends EditRecord
{
    protected static string $resource = GruppoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
