<?php

namespace App\Filament\Resources\CajaMovimientoResource\Pages;

use App\Filament\Resources\CajaMovimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCajaMovimiento extends CreateRecord
{
    protected static string $resource = CajaMovimientoResource::class;
    protected static bool $canCreateAnother = false;
    
}
