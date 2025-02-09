<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaServicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_cliente',
        'servicio_id',
        'fecha',
        'monto',
        'user_id'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'codigo_cliente', 'codigo');
    }

    public function servicio()
    {
        return $this->belongsTo(ServicioAdicional::class, 'servicio_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
