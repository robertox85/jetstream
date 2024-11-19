<?php

namespace App\Filament\Resources\ContabilitaResource\Pages;

use App\Filament\Resources\ContabilitaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContabilita extends EditRecord
{
    protected static string $resource = ContabilitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
