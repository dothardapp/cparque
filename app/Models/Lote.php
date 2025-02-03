<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'sector_id',
        'numero',
        'descripcion',
    ];

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
}
