<?php

namespace App\Filament\Resources\AssistitoResource\Pages;

use App\Filament\Resources\AssistitoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssistito extends EditRecord
{
    protected static string $resource = AssistitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
