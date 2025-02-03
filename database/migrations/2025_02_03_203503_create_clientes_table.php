<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 15)->unique();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->date('fecha_nacimiento');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientes');
    }
};
