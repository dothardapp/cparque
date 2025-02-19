<?php

namespace App\Actions;

use App\Models\Expensa;
use App\Models\CajaMovimiento;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RegistrarPagoExpensas
{
    public static function make(): Action
    {
        return Action::make('registrarPagoExpensas')
            ->label('Registrar Pago')
            ->icon('heroicon-m-currency-dollar')
            ->modal()
            ->form([
                // Selección del Año (solo muestra años con meses pendientes)
                Select::make('anio')
                    ->label('Seleccionar Año')
                    ->options(fn($livewire) => Expensa::where('cliente_id', $livewire->ownerRecord->id)
                        ->where('estado', '!=', 'pagado')
                        ->distinct()
                        ->orderBy('anio', 'desc')
                        ->pluck('anio', 'anio'))
                    ->searchable()
                    ->reactive()
                    ->required(),

                // Selección de Meses (solo meses pendientes del año seleccionado)
                Select::make('expensas_pagadas')
                    ->label('Seleccionar Meses')
                    ->options(fn(callable $get, $livewire) =>
                        Expensa::where('cliente_id', $livewire->ownerRecord->id)
                            ->where('estado', '!=', 'pagado')
                            ->where('anio', $get('anio'))
                            ->orderBy('mes')
                            ->get()
                            ->mapWithKeys(fn($expensa) => [
                                $expensa->id => sprintf(
                                    '%s / %s - ARS %s',
                                    self::nombreMes($expensa->mes),
                                    $expensa->anio,
                                    number_format($expensa->saldo, 2, ',', '.')
                                )
                            ])
                    )
                    ->multiple()
                    ->searchable()
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(fn(callable $get, callable $set) =>
                        $set('monto_total', Expensa::whereIn('id', array_filter((array) $get('expensas_pagadas')))->sum('saldo'))
                    ),

                // Monto Total
                TextInput::make('monto_total')
                    ->label('Monto Total')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->reactive()
                    ->extraAttributes(['class' => 'text-lg font-bold']),

                // Medio de Pago
                Select::make('medio_pago')
                    ->label('Medio de Pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'transferencia' => 'Transferencia',
                        'tarjeta' => 'Tarjeta',
                        'otro' => 'Otro',
                    ])
                    ->required(),
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

                    $clienteId = $record->id;
                    $expensasSeleccionadas = Expensa::whereIn('id', $data['expensas_pagadas'])->get();
                    $totalPago = $expensasSeleccionadas->sum('saldo');

                    // Obtener la primera parcela asociada a las expensas pagadas
                    $parcela = $expensasSeleccionadas->first()->parcela ?? null;

                    // Generar descripción detallada
                    $concepto = sprintf(
                        'Pago de Expensas - Cliente: %s %s | Parcela: %s | Meses: %s',
                        $record->nombre,
                        $record->apellido,
                        $parcela?->descripcion ?? 'Sin parcela',
                        $expensasSeleccionadas->map(fn($e) => self::nombreMes($e->mes) . ' ' . $e->anio)->implode(', ')
                    );

                    // Obtener el próximo número de recibo
                    $ultimoRecibo = CajaMovimiento::max('numero_recibo') ?? 0;
                    $numeroRecibo = str_pad($ultimoRecibo + 1, 6, '0', STR_PAD_LEFT);

                    // ✅ Generar un código QR único para este recibo
                    $qrCode = hash('sha256', uniqid($numeroRecibo, true));

                    // Crear movimiento en caja
                    $movimiento = new CajaMovimiento([
                        'cliente_id' => $clienteId,
                        'fecha_y_hora' => now(),
                        'tipo' => 'pago_expensas',
                        'monto' => $totalPago,
                        'medio_pago' => $data['medio_pago'],
                        'numero_recibo' => $numeroRecibo,
                        'concepto' => $concepto,
                        'user_id' => Auth::id(),
                        'qr_code' => $qrCode, // ✅ Guardamos el QR
                    ]);

                    // Relacionar con la parcela si existe
                    if ($parcela) {
                        $movimiento->referencia()->associate($parcela);
                    }

                    $movimiento->save();

                    // Actualizar expensas como pagadas
                    foreach ($expensasSeleccionadas as $expensa) {
                        $expensa->update(['estado' => 'pagado', 'saldo' => 0]);
                    }

                    Notification::make()
                        ->title('Pago registrado correctamente')
                        ->success()
                        ->send();
                });
            });
    }

    private static function nombreMes($numeroMes): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$numeroMes] ?? 'Desconocido';
    }
}
