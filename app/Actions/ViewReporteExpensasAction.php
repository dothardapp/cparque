<?php

namespace App\Actions;

use Filament\Tables\Actions\Action;

class ViewReporteExpensasAction
{
    public static function make(int $recordId): Action
    {
        return Action::make('generarReporte')
            ->label('Imprimir')
            ->icon('heroicon-o-printer')
            ->url(fn () => route('cliente.reporte', ['id' => $recordId])) // ✅ Corregido 'id' en lugar de 'record'
            ->openUrlInNewTab();
    }
}
