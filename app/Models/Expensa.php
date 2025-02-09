<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expensa extends Model
{
    use HasFactory;

    protected $fillable = [
        'parcela_id',
        'cliente_id',
        'anio',
        'mes',
        'monto',
        'saldo',
        'estado',
        'user_id'
    ];

    public function parcela()
    {
        return $this->belongsTo(Parcela::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
