<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Resources\Pages\CreateRecord;


class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;
    protected static bool $canCreateAnother = false;
}
