<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    use HasFactory;

    protected $table = 'parcelas';

    protected $fillable = [
        'lote_id',
        'cliente_id',
        'numero',
        'estado',
        'descripcion',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function expensas()
    {
        return $this->hasMany(Expensa::class);
    }
}
