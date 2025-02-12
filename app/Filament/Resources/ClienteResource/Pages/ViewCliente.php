<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewCliente extends ViewRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('volver')
                ->label('Volver')
                ->url(fn() => ClienteResource::getUrl()) // Redirige al listado de clientes
                ->icon('heroicon-o-arrow-left')
                ->color('secondary'),
        ];
    }
}
