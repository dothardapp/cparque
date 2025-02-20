<?php

namespace App\Filament\Widgets;

use App\Models\Cliente;
use Filament\Support\Enums\Alignment;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ClientesMorosos extends BaseWidget
{
    protected static ?string $heading = 'TOP 10 Clientes Morosos';
    protected static ?int $sort = 2;
    protected static string $height = '150px'; // Ajustar la altura del widget

    protected function getTableQuery(): Builder
    {
        return Cliente::query()
            ->selectRaw('
                clientes.id AS id,
                clientes.dni,
                CONCAT(clientes.nombre, " ", clientes.apellido) AS cliente,
                SUM(expensas.saldo) AS deuda_total,
                COUNT(expensas.id) AS expensas_impagas
            ')
            ->join('expensas', 'clientes.id', '=', 'expensas.cliente_id')
            ->where('expensas.estado', '!=', 'pagado')
            ->groupBy('clientes.id', 'clientes.dni', 'clientes.nombre', 'clientes.apellido')
            ->orderByDesc('deuda_total')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('cliente')
                ->label('Cliente')
                ->sortable()
                ->url(fn($record) => "/admin/clientes/{$record->id}"), // Se abre en la misma ventana

            TextColumn::make('deuda_total')
                ->label('Deuda Total')
                ->money('ARS')
                ->sortable()
                ->color(fn($record) => match (true) {
                    $record->deuda_total >= 650000 => 'danger',   // Rojo si la deuda es mayor o igual a 100,000
                    $record->deuda_total >= 500000  => 'warning',  // Naranja si la deuda está entre 50,000 y 99,999
                    $record->deuda_total >= 250000  => 'info',     // Azul si la deuda está entre 10,000 y 49,999
                    default => 'success',                         // Verde si la deuda es menor a 10,000
                }),

            TextColumn::make('expensas_impagas')
                ->label('EXP Impagas')
                ->sortable()
                ->alignment(Alignment::End),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [
            3 => '3',   // Predeterminado
            5 => '5',
            10 => '10',
        ];
    }
}
