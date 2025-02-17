<?php

namespace App\Filament\Resources\CajaMovimientoResource\Pages;

use App\Filament\Resources\CajaMovimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCajaMovimientos extends ListRecords
{
    protected static string $resource = CajaMovimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
