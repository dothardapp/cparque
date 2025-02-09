<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CantidadInhumacionesChart extends ChartWidget
{
    protected static ?string $heading = 'Inhumaciones por Año';

    protected function getData(): array
    {
        $data = DB::table('inhumados')
            ->selectRaw('YEAR(fecha_inhumacion) as year, COUNT(*) as count')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        return [
            'labels' => $data->pluck('year')->toArray(),
            'datasets' => [
                [
                    'label' => 'Cantidad de Inhumaciones',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Gráfico de barras
    }
}
