<?php

namespace App\Filament\Resources\InhumadoResource\Pages;

use App\Filament\Resources\InhumadoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInhumado extends ViewRecord
{
    protected static string $resource = InhumadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
