<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    use HasFactory;

    protected $table = 'contactos';

    protected $fillable = [
        'cliente_id',
        'tipo',
        'valor',
        'comentario',
        'principal',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
