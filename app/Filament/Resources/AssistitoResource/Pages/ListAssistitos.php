<?php

namespace App\Filament\Resources\AssistitoResource\Pages;

use App\Filament\Resources\AssistitoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssistitos extends ListRecords
{
    protected static string $resource = AssistitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
