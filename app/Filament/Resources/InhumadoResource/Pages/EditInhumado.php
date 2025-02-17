<?php

namespace App\Filament\Resources\InhumadoResource\Pages;

use App\Filament\Resources\InhumadoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInhumado extends EditRecord
{
    protected static string $resource = InhumadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
