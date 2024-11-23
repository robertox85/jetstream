<?php

namespace App\Filament\Resources\PraticaResource\Pages;

use App\Filament\Resources\PraticaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePratica extends CreateRecord
{
    protected static string $resource = PraticaResource::class;

    protected ?string $maxContentWidth = 'full';
}
