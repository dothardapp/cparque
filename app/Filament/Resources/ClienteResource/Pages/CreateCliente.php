<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use App\Models\Cliente;
use App\Models\Parcela;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;
    protected static bool $canCreateAnother = false;

    /**
     * Maneja la creación del Cliente.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1️⃣ Crear el cliente
            $cliente = Cliente::create($data);

            // 2️⃣ Si se seleccionó una parcela, verificar que esté libre y asignarla
            if (! empty($data['parcela_id'])) {
                $parcela = Parcela::where('id', $data['parcela_id'])->lockForUpdate()->first();
                if ($parcela && $parcela->estado === 'libre') {
                    $parcela->update([
                        'cliente_id' => $cliente->id,
                        'estado' => 'ocupada',
                    ]);
                }
            }

            return $cliente;
        });
    }

}
