<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inhumado extends Model
{
    use HasFactory;

    protected $table = 'inhumados';

    protected $fillable = [
        'cliente_id',
        'parcela_id',
        'nivel',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'fecha_inhumacion',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function parcela()
    {
        return $this->belongsTo(Parcela::class);
    }
}
