<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parcela_id');
            $table->unsignedBigInteger('expensa_id');
            $table->dateTime('fecha_pago')->default(now());
            $table->decimal('monto_pagado', 10, 2);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('parcela_id')->references('id')->on('parcelas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('expensa_id')->references('id')->on('expensas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos');
    }
};
