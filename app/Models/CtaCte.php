<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ctacte extends Model
{
    use HasFactory;

    protected $table = 'ctasctes';

    protected $fillable = [
        'cliente_id',
        'fecha',
        'tipo',
        'debe',
        'haber',
        'anio',
        'mes',
        'estado',
        'recibo',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
