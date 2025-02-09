<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('ventas_servicios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('codigo_cliente');
            $table->unsignedBigInteger('servicio_id');
            $table->dateTime('fecha')->default(now());
            $table->decimal('monto', 10, 2);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('codigo_cliente')->references('id')->on('clientes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('servicio_id')->references('id')->on('servicios_adicionales')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ventas_servicios');
    }
};
