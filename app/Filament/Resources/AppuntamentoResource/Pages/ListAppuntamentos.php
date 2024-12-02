<?php

namespace App\Filament\Resources\AppuntamentoResource\Pages;

use App\Filament\Resources\AppuntamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppuntamentos extends ListRecords
{
    protected static string $resource = AppuntamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
