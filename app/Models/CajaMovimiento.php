<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CajaMovimiento extends Model
{
    use HasFactory;

    protected $table = 'caja_movimientos';

    protected $fillable = [
        'cliente_id',
        'fecha_y_hora',
        'tipo',
        'concepto',
        'monto',
        'medio_pago',
        'user_id',
        'numero_recibo',
        'qr_code'
    ];

    protected $casts = [
        'fecha_y_hora' => 'datetime', // ✅ Convierte automáticamente a `Carbon`
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($movimiento) {
            // Generar el número de recibo como antes
            $ultimoRecibo = self::max('numero_recibo');
            $nuevoNumero = $ultimoRecibo ? (intval($ultimoRecibo) + 1) : 1;
            $movimiento->numero_recibo = str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);

            //✅ Generar el QR Code ÚNICO basado en ID + Timestamp solo si aún no tiene uno
            if (!$movimiento->qr_code) {
                $movimiento->qr_code = hash('sha256', uniqid($movimiento->numero_recibo, true));
            }
        });
    }


    public function referencia(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expensas()
    {
        return $this->belongsToMany(Expensa::class, 'caja_movimiento_expensas', 'caja_movimiento_id', 'expensa_id')
            ->withPivot('monto_pagado')
            ->withTimestamps();
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
