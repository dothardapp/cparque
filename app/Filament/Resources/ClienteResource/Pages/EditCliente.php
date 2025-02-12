<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use App\Models\Parcela;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Cancelar'),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Maneja la actualización del registro (cliente).
     * Firma correcta: handleRecordUpdate(Model $record, array $data)
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::transaction(function () use ($data, $record) {
            // 1️⃣ Actualiza los datos del cliente
            $record->update($data);

            // 2️⃣ Verifica si se eligió una parcela nueva
            if (! empty($data['parcela_id'])) {
                // 2.1 Liberar la parcela anterior (si la tenía)
                $oldParcelaId = $record->getOriginal('parcela_id');
                if ($oldParcelaId && $oldParcelaId != $data['parcela_id']) {
                    Parcela::where('id', $oldParcelaId)->update([
                        'estado'     => 'libre',
                        'cliente_id' => 185000,
                    ]);
                }

                // 2.2 Ocupar la nueva
                Parcela::where('id', $data['parcela_id'])->update([
                    'estado'     => 'ocupada',
                    'cliente_id' => $record->id,
                ]);
            } else {
                // 3️⃣ Si se quitó la parcela, libera la anterior si existía
                $oldParcelaId = $record->getOriginal('parcela_id');
                if ($oldParcelaId) {
                    Parcela::where('id', $oldParcelaId)->update([
                        'estado'     => 'libre',
                        'cliente_id' => 185000,
                    ]);
                }
            }
        });

        return $record;
    }
}
