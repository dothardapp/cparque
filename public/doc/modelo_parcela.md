# Creación del Modelo Parcela en Laravel con Migración

Este documento registra los pasos para la creación del modelo `Parcela` en el proyecto `cparque` con Laravel.

## Paso 1: Crear el modelo y la migración

Ejecutar en la terminal:

```sh
php artisan make:model Parcela -m
```

Este comando genera:

-   El modelo en `app/Models/Parcela.php`
-   La migración en `database/migrations/`

---

## Paso 2: Definir el modelo `Parcela`

Editar el archivo `app/Models/Parcela.php` para agregar los atributos `fillable` y la relación con `Lote`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    use HasFactory;

    protected $table = 'parcelas';

    protected $fillable = [
        'lote_id',
        'numero',
        'estado',
        'descripcion',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }
}
```

---

## Paso 3: Editar la migración

Ubicar el archivo de migración en `database/migrations/` y editarlo con la siguiente estructura:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
```

---

## Paso 4: Ejecutar la migración

Para aplicar la migración y crear la tabla `parcelas`, ejecutar en la terminal:

```sh
php artisan migrate
```
