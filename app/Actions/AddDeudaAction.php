<?php

namespace App\Actions;

use App\Models\Expensa;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class AddDeudaAction
{
    public static function make(): Action
    {
        return Action::make('addDeuda')
            ->label('Generar Deuda')
            ->icon('heroicon-m-document-currency-dollar')
            ->form([
                TextInput::make('desde_anio')
                    ->label('Desde Año')
                    ->required()
                    ->numeric(),

                TextInput::make('hasta_anio')
                    ->label('Hasta Año')
                    ->required()
                    ->numeric(),

                TextInput::make('monto')
                    ->label('Monto a aplicar')
                    ->required()
                    ->numeric()
                    ->prefix('ARS '),
            ])
            ->action(function (array $data, $livewire): void {
                DB::transaction(function () use ($data, $livewire) {
                    $record = $livewire->ownerRecord;
                    if (!$record) {
                        Notification::make()
                            ->title('Error')
                            ->body('No se encontró el cliente.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $desdeAnio = $data['desde_anio'];
                    $hastaAnio = $data['hasta_anio'];
                    $monto = $data['monto'];

                    // Validar que "desde_anio" sea menor o igual a "hasta_anio"
                    if ($desdeAnio > $hastaAnio) {
                        Notification::make()
                            ->title('Error en los años')
                            ->body('El año inicial no puede ser mayor que el año final.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $clienteId = $record->id;
                    $parcelas = $record->parcelas;
                    $expensasExistentes = []; // Guardará las expensas ya existentes

                    foreach ($parcelas as $parcela) {
                        for ($anio = $desdeAnio; $anio <= $hastaAnio; $anio++) {
                            for ($mes = 1; $mes <= 12; $mes++) {
                                $existe = Expensa::where('parcela_id', $parcela->id)
                                    ->where('anio', $anio)
                                    ->where('mes', $mes)
                                    ->exists();

                                if ($existe) {
                                    $expensasExistentes[] = "Parcela {$parcela->id} - {$anio}/{$mes}";
                                    continue;
                                }

                                Expensa::create([
                                    'parcela_id' => $parcela->id,
                                    'cliente_id' => $clienteId,
                                    'anio' => $anio,
                                    'mes' => $mes,
                                    'monto' => $monto,
                                    'saldo' => $monto,
                                    'estado' => 'pendiente',
                                    'user_id' => auth()->id(),
                                ]);
                            }
                        }
                    }

                    // Si hubo expensas ya existentes, enviar una sola notificación con el resumen
                    if (!empty($expensasExistentes)) {
                        Notification::make()
                            ->title('Expensas ya existentes')
                            ->body("Las siguientes expensas ya estaban registradas:\n" . implode("\n", $expensasExistentes))
                            ->warning()
                            ->send();
                    }

                    Notification::make()
                        ->title('Deuda generada correctamente')
                        ->success()
                        ->send();
                });
            });
    }
}
