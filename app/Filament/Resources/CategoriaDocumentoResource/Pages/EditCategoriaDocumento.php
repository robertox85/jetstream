<?php

namespace App\Filament\Resources\CategoriaDocumentoResource\Pages;

use App\Filament\Resources\CategoriaDocumentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaDocumento extends EditRecord
{
    protected static string $resource = CategoriaDocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
