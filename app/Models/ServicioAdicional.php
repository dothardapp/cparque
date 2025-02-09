<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioAdicional extends Model
{
    use HasFactory;

    protected $fillable = [
        'descripcion',
        'precio'
    ];

    public function ventas()
    {
        return $this->hasMany(VentaServicio::class, 'servicio_id');
    }
}
