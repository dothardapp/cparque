<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use App\Models\Cliente;
use App\Models\Parcela;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;
    protected static bool $canCreateAnother = false;
}
