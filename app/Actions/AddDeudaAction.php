<?php

namespace App\Actions;

use App\Models\Expensa;
use Filament\Forms\Components\Select;
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
                Select::make('desde_anio')
                    ->label('Desde Año')
                    ->options(array_combine(range(1994, 2025), range(1994, 2025)))
                    ->required(),

                Select::make('hasta_anio')
                    ->label('Hasta Año')
                    ->options(array_combine(range(1994, 2025), range(1994, 2025)))
                    ->required(),

                Select::make('hasta_mes')
                    ->label('Hasta Mes en el último año')
                    ->options([
                        '1'  => '1 - Enero',
                        '2'  => '2 - Febrero',
                        '3'  => '3 - Marzo',
                        '4'  => '4 - Abril',
                        '5'  => '5 - Mayo',
                        '6'  => '6 - Junio',
                        '7'  => '7 - Julio',
                        '8'  => '8 - Agosto',
                        '9'  => '9 - Septiembre',
                        '10' => '10 - Octubre',
                        '11' => '11 - Noviembre',
                        '12' => '12 - Diciembre',
                    ])
                    ->default(12)
                    ->required(),

                TextInput::make('monto')
                    ->label('Monto a aplicar')
                    ->required()
                    ->prefix('ARS ')
                    ->minValue(1),
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
                    $hastaMes = $data['hasta_mes'];
                    $monto = $data['monto'];

                    if ($monto <= 0) {
                        Notification::make()
                            ->title('Monto inválido')
                            ->body('El monto a aplicar no puede ser negativo.')
                            ->danger()
                            ->send();
                        return;
                    }

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
                    $expensasExistentes = [];

                    foreach ($parcelas as $parcela) {
                        for ($anio = $desdeAnio; $anio <= $hastaAnio; $anio++) {
                            $mesFinal = ($anio == $hastaAnio) ? $hastaMes : 12;

                            for ($mes = 1; $mes <= $mesFinal; $mes++) {
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
