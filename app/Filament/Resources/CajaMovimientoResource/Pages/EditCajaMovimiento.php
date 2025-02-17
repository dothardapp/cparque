<?php

namespace App\Filament\Resources\CajaMovimientoResource\Pages;

use App\Filament\Resources\CajaMovimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCajaMovimiento extends EditRecord
{
    protected static string $resource = CajaMovimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
