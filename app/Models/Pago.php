<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'parcela_id',
        'expensa_id',
        'fecha_pago',
        'monto_pagado',
        'user_id'
    ];

    public function expensa()
    {
        return $this->belongsTo(Expensa::class);
    }

    public function parcela()
    {
        return $this->belongsTo(Parcela::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
