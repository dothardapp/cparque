<?php

namespace App\Filament\Resources\InhumadoResource\Pages;

use App\Filament\Resources\InhumadoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInhumados extends ListRecords
{
    protected static string $resource = InhumadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
