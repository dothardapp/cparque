<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parcelas', function (Blueprint $table) {
            $table->foreignId('cliente_id')
                ->default(185000)
                ->constrained('clientes')
                ->onDelete('cascade')
                ->comment('Identifica el cliente propietario de la parcela.');
        });
    }

    public function down()
    {
        Schema::table('parcelas', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');
        });
    }
};
