<?php

namespace App\Filament\Resources\PraticaResource\Pages;

use App\Filament\Resources\PraticaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mansoor\FilamentVersionable\Page\RevisionsAction;

class EditPratica extends EditRecord
{
    protected static string $resource = PraticaResource::class;

    protected ?string $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
            RevisionsAction::make(),
            // ...
        ];
    }
}
