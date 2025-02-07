<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'dni',
        'codigo',
        'nombre',
        'apellido',
        'fecha_nacimiento',
    ];

    protected $casts = [
        'codigo' => 'integer',
        'fecha_nacimiento' => 'date',
    ];

    /**
     * Relación: Un cliente puede tener múltiples parcelas.
     */
    public function parcelas()
    {
        return $this->hasMany(Parcela::class);
    }

    /**
     * Relación: Un cliente puede tener múltiples movimientos en cuenta corriente.
     */
    public function cuentasCorrientes()
    {
        return $this->hasMany(Ctacte::class);
    }

    /**
     * Relación: Un cliente puede tener múltiples contactos.
     */
    public function contactos()
    {
        return $this->hasMany(Contacto::class);
    }

    /**
     * Relación: Un cliente puede estar inhumado en diferentes parcelas.
     */
    public function inhumados()
    {
        return $this->hasMany(Inhumado::class);
    }

    public function getNombreAttribute($value)
    {
        return ucwords(strtolower($value));
    }

    public function getApellidoAttribute($value)
    {
        return ucwords(strtolower($value));
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($cliente) {
            DB::transaction(function () use ($cliente) {
                // 1️⃣ Obtener la parcela asignada al cliente
                $parcela = \App\Models\Parcela::where('cliente_id', $cliente->id)->first();

                if ($parcela) {
                    // 2️⃣ Liberar la parcela (estado a 'libre' y cliente_id a 185000)
                    $parcela->update([
                        'estado' => 'libre',
                        'cliente_id' => 185000,
                    ]);
                }

                // 3️⃣ Borrar los inhumados asociados al cliente
                \App\Models\Inhumado::where('cliente_id', $cliente->id)->delete();

                // 4️⃣ Enviar una notificación
                Notification::make()
                    ->title('Cliente eliminado')
                    ->body("Se eliminó el cliente {$cliente->nombre} {$cliente->apellido}. La parcela fue liberada y los inhumados asociados han sido eliminados.")
                    ->success()
                    ->send();
            });
        });
    }

}
