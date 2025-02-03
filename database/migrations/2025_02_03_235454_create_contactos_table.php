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
            $table->enum('tipo', ['domicilio', 'telefono', 'email'])->comment('Tipo de contacto: domicilio, teléfono o email');
            $table->string('valor', 255)->comment('Valor del contacto (dirección, número o email)');
            $table->string('comentario', 255)->nullable()->comment('Información adicional sobre el contacto');
            $table->boolean('principal')->default(false)->comment('Indica si es el contacto principal del cliente');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contactos');
    }
};
