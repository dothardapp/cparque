<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Cliente;
use App\Models\Inhumado;
use App\Models\Parcela;

class ClientesOverview extends BaseWidget
{

    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        return [
            Stat::make('Clientes', Cliente::query()->count('codigo'))
                ->description('Total de clientes activos en el sistema')
                ->color('primary')
                ->icon('heroicon-o-user-group'),

            Stat::make('Inhumados', Inhumado::query()->count('nombre'))
                ->description('Cantidad total de inhumaciones registradas')
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-check'),

            Stat::make('Parcelas ocupadas', Parcela::query()->where('estado', 'ocupada')->count())
                ->description('Parcelas actualmente con inhumaciones')
                ->color('danger')
                ->icon('heroicon-o-building-library'),

            Stat::make('Parcelas libres', Parcela::query()->where('estado', 'libre')->count())
                ->description('Parcelas aún disponibles para nuevas inhumaciones')
                ->color('success')
                ->icon('heroicon-o-map'),
        ];
    }
}
