<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanPagoCuota extends Model
{
    use HasFactory;

    protected $table = 'plan_pago_cuotas';

    protected $fillable = [
        'plan_pago_id', 'fecha_vencimiento', 'monto_original', 'monto_con_interes', 'pagado', 'fecha_pago'
    ];

    public function planPago(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_pago_id');
    }
}
