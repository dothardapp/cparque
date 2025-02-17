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
        Schema::create('planes_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->decimal('total_deuda', 10, 2);
            $table->integer('cantidad_cuotas');
            $table->decimal('monto_cuota', 10, 2);
            $table->decimal('saldo_pendiente', 10, 2);
            $table->decimal('tasa_interes', 5, 2)->default(0); // Puede ser 0 si no hay interés
            $table->enum('tipo_interes', ['fijo', 'mensual'])->nullable();
            $table->decimal('total_con_interes', 10, 2);
            $table->date('fecha_inicio');
            $table->enum('estado', ['activo', 'cancelado'])->default('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_pago');
    }
};
