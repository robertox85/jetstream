<?php

namespace App\Filament\Resources\ControparteResource\Pages;

use App\Filament\Resources\ControparteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditControparte extends EditRecord
{
    protected static string $resource = ControparteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
