<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /// Tabla de movimientos de caja
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_recibo')->unique();
            $table->dateTime('fecha_y_hora')->default(now());

            // Nuevo tipo de movimiento más detallado
            $table->enum('tipo', [
                'pago_expensas',     // Pago normal de expensas
                'pago_plan_pago',    // Pago de expensas bajo plan de pagos
                'venta_parcela',     // Venta de una parcela
                'otro_ingreso',      // Otro tipo de ingreso
                'retiro_fondos',     // Retiro de dinero o pago de proveedor
                'otro_egreso'        // Cualquier otro egreso
            ]);

            $table->string('concepto'); // Descripción breve
            $table->decimal('monto', 10, 2);
            $table->enum('medio_pago', ['efectivo', 'transferencia', 'tarjeta', 'otro']);
            $table->foreignId('user_id')->constrained('users');

            // Relación con Cliente (para saber a quién se le registró el pago o movimiento)
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();

            // Relación opcional con otro modelo
            $table->nullableMorphs('referencia'); // Para relacionar con pagos, planes de pago, ventas, etc.

            // Nuevo campo para información extra
            $table->text('detalle')->nullable(); // Puede usarse para guardar notas o detalles adicionales

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};
