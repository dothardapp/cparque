<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanPago extends Model
{
    use HasFactory;

    protected $table = 'planes_pago';

    protected $fillable = [
        'cliente_id', 'total_deuda', 'cantidad_cuotas', 'monto_cuota', 'saldo_pendiente', 'tasa_interes', 'tipo_interes', 'total_con_interes', 'fecha_inicio', 'estado'
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(PlanPagoCuota::class, 'plan_pago_id');
    }

    public function expensas(): HasMany
    {
        return $this->hasMany(PlanPagoExpensa::class, 'plan_pago_id');
    }
}
