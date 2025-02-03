<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parcelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('cascade');
            $table->integer('numero');
            $table->enum('estado', ['libre', 'ocupada'])->default('libre')->comment('Identifica si una parcela está ocupada o libre');
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parcelas');
    }
};
