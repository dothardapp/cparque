<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ctasctes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->date('fecha');
            $table->string('tipo', 50);
            $table->decimal('debe', 10, 2)->default(0);
            $table->decimal('haber', 10, 2)->default(0);
            $table->integer('anio');
            $table->integer('mes');
            $table->string('estado', 50);
            $table->string('recibo', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ctasctes');
    }
};
