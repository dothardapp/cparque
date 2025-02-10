<?php

namespace App\Filament\Resources\ClienteResource\Widgets;

use App\Models\Cliente;
use App\Models\Inhumado;
use App\Models\Parcela;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Clientes', Cliente::query()->count('codigo')),
            Stat::make('Inhumados', Inhumado::query()->count('nombre')),
            Stat::make('Parcelas ocupadas', Parcela::query()->where('estado', 'ocupada')->count()),
            Stat::make('Parcelas libres', Parcela::query()->where('estado', 'libre')->count()),
        ];
    }
}
