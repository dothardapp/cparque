<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('caja_movimiento_expensas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('caja_movimiento_id')->constrained('caja_movimientos')->onDelete('cascade');
        $table->foreignId('expensa_id')->constrained('expensas')->onDelete('cascade');
        $table->decimal('monto_pagado', 10, 2);
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('caja_movimiento_expensas');
}

};
