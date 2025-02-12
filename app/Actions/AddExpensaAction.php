<?php

namespace App\Actions;

use App\Models\Expensa;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class AddExpensaAction
{
    public static function make(): Action
    {
        return Action::make('addExpensa')
            ->label('Registrar Expensa')
            ->icon('heroicon-o-plus')
            ->form([
                Select::make('parcela_id')
                    ->label('Parcela')
                    ->options(fn($livewire) =>
                        $livewire->ownerRecord?->parcelas()->pluck('descripcion', 'id')->toArray() ?? []
                    )
                    ->searchable()
                    ->required(),

                TextInput::make('anio')
                    ->label('Año')
                    ->required()
                    ->numeric(),

                TextInput::make('mes')
                    ->label('Mes')
                    ->required()
                    ->numeric(),

                TextInput::make('monto')
                    ->label('Monto')
                    ->required()
                    ->numeric()
                    ->prefix('ARS '),

                TextInput::make('saldo')
                    ->label('Saldo')
                    ->required()
                    ->numeric()
                    ->prefix('ARS '),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'pagado parcialmente' => 'Pagado Parcialmente',
                        'pagado' => 'Pagado',
                    ])
                    ->required(),

                Hidden::make('cliente_id'),
                Hidden::make('user_id')->default(auth()->id()),
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

                    // Verificar si ya existe una expensa con los mismos datos
                    $existe = Expensa::where('parcela_id', $data['parcela_id'])
                        ->where('anio', $data['anio'])
                        ->where('mes', $data['mes'])
                        ->exists();

                    if ($existe) {
                        Notification::make()
                            ->title('Error: Expensa duplicada')
                            ->body("Ya existe una expensa para la parcela {$data['parcela_id']} en {$data['anio']}/{$data['mes']}.")
                            ->danger()
                            ->send();
                        return;
                    }

                    // Si no existe, se crea la nueva expensa
                    Expensa::create([
                        'parcela_id' => $data['parcela_id'],
                        'cliente_id' => $record->id,
                        'anio' => $data['anio'],
                        'mes' => $data['mes'],
                        'monto' => $data['monto'],
                        'saldo' => $data['saldo'],
                        'estado' => $data['estado'],
                        'user_id' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('Expensa registrada')
                        ->success()
                        ->send();
                });
            });
    }
}
