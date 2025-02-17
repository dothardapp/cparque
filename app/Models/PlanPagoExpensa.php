<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanPagoExpensa extends Model
{
    use HasFactory;

    protected $table = 'planes_pago_expensas';

    protected $fillable = [
        'plan_pago_id', 'expensa_id', 'monto_original', 'saldo_pendiente'
    ];

    public function planPago(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_pago_id');
    }

    public function expensa(): BelongsTo
    {
        return $this->belongsTo(Expensa::class, 'expensa_id');
    }
}
