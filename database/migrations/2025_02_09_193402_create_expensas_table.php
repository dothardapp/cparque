<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('expensas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parcela_id');
            $table->unsignedBigInteger('cliente_id'); // Cliente dueño de la parcela
            $table->integer('anio');
            $table->integer('mes');
            $table->decimal('monto', 10, 2);
            $table->decimal('saldo', 10, 2)->default(0);
            $table->enum('estado', ['pendiente', 'pagado parcialmente', 'pagado'])->default('pendiente');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->unique(['parcela_id', 'anio', 'mes']);
            $table->foreign('parcela_id')->references('id')->on('parcelas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('expensas');
    }
};
