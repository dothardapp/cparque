<?php

namespace App\Filament\Widgets;

use App\Models\Expensa;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DeudaTotalWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalDeuda = Expensa::where('estado', '!=', 'pagado')->sum('saldo');

        return [
            Stat::make('Deuda Total', "ARS " . number_format($totalDeuda, 2, ',', '.'))
                ->description('Suma total de todas las deudas de los clientes')
                ->color('danger') // Rojo para destacar la deuda
                ->icon('heroicon-m-document-currency-dollar')
                ->chart([0, 25000, 22000, 23000, 22500]) // Simula variaciones en la deuda
        ];
    }
}
