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
        'numero',
        'estado',
        'descripcion',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }
}
