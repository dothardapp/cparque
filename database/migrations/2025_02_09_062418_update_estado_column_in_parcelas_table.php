<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('parcelas', function (Blueprint $table) {
            $table->enum('estado', ['libre', 'comprada', 'ocupada'])
                  ->default('libre')
                  ->comment('Estado de la parcela')
                  ->change();
        });
    }

    public function down()
    {
        Schema::table('parcelas', function (Blueprint $table) {
            $table->enum('estado', ['libre', 'ocupada'])
                  ->default('libre')
                  ->comment('Identifica si una parcela está ocupada o libre')
                  ->change();
        });
    }
};
