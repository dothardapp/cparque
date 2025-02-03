<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inhumados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('parcela_id')->constrained('parcelas')->onDelete('cascade');
            $table->enum('nivel', ['primer_nivel', 'segundo_nivel', 'tercer_nivel'])
                ->comment('Ubicación dentro de la parcela: primer nivel (más profundo), segundo nivel o tercer nivel (más superficial).');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->date('fecha_nacimiento')->default('1970-01-01')->comment('Fecha de nacimiento por defecto.');
            $table->date('fecha_inhumacion');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inhumados');
    }
};
