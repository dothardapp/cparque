<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use App\Models\Cliente;
use App\Models\Parcela;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $cliente = \DB::transaction(function () use ($data) {
            // 1️⃣ Crear el cliente
            $cliente = \App\Models\Cliente::create($data);

            // 2️⃣ Actualizar la parcela asignándole el cliente_id
            if (!empty($data['parcela_id'])) {
                \App\Models\Parcela::where('id', $data['parcela_id'])
                    ->update(['cliente_id' => $cliente->id]);
            }

            // 3️⃣ Insertar inhumados con el nivel calculado
            if (!empty($data['inhumados'])) {
                foreach ($data['inhumados'] as $inhumado) {
                    // Obtener el nivel basado en la cantidad de inhumados en la parcela
                    $cantidadInhumados = \App\Models\Inhumado::where('parcela_id', $data['parcela_id'])->count();

                    $niveles = ['primer_nivel', 'segundo_nivel', 'tercer_nivel'];
                    $nivel = $cantidadInhumados < 3 ? $niveles[$cantidadInhumados] : 'tercer_nivel'; // Máximo tercer nivel

                    // Insertar el inhumado con el nivel calculado
                    \App\Models\Inhumado::create([
                        'cliente_id' => $cliente->id,
                        'parcela_id' => $data['parcela_id'],
                        'nivel' => $nivel,
                        'nombre' => $inhumado['nombre'],
                        'apellido' => $inhumado['apellido'],
                        'fecha_nacimiento' => $inhumado['fecha_nacimiento'] ?? '1970-01-01',
                        'fecha_inhumacion' => $inhumado['fecha_inhumacion'],
                    ]);
                }
            }

            return $cliente; // Retorna el cliente creado dentro de la transacción
        });

        // 4️⃣ Notificación de éxito
        Notification::make()
            ->title('Cliente creado con éxito')
            ->success()
            ->send();

        return $cliente; // Retorna el cliente fuera de la transacción
    }
}
