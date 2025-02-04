<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('barrio', 100)->nullable()->comment('Barrio donde reside el cliente');
            $table->string('domicilio', 255)->comment('Dirección exacta del cliente');
            $table->string('telefono', 50)->nullable()->comment('Número de contacto del cliente');
            $table->string('email', 100)->nullable()->comment('Correo electrónico del cliente');
            $table->string('comentario', 255)->nullable()->comment('Información adicional relevante');
            $table->boolean('principal')->default(false)->comment('Indica si es el contacto principal del cliente');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contactos');
    }
};
