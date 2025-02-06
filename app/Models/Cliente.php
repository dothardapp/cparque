<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
