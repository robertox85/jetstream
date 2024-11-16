<?php

namespace App\Filament\Resources\UdienzaResource\Pages;

use App\Filament\Resources\UdienzaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUdienza extends EditRecord
{
    protected static string $resource = UdienzaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
