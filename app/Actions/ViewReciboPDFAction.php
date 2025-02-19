<?php

namespace App\Actions;

use Filament\Actions\Action;

class ViewReciboPDFAction
{
    public static function make(int $recordId): Action
    {
        return Action::make('ver_pdf')
            ->label('Ver Recibo')
            ->icon('heroicon-o-document-text')
            ->url(fn () => route('caja-movimiento.pdf', ['record' => $recordId]))
            ->openUrlInNewTab(); // 📌 Abre el PDF en una nueva pestaña
    }
}
