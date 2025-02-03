# Creación del Modelo Lote en Laravel con Migración

Este documento registra los pasos para la creación del modelo `Lote` en el proyecto `cparque` con Laravel.

## Paso 1: Crear el modelo y la migración

Ejecutar en la terminal:

```sh
php artisan make:model Lote -m
```

Este comando genera:

-   El modelo en `app/Models/Lote.php`
-   La migración en `database/migrations/`

---

## Paso 2: Definir el modelo `Lote`

Editar el archivo `app/Models/Lote.php` para agregar los atributos `fillable` y la relación con `Sector`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'sector_id',
        'numero',
        'descripcion',
    ];

    public function sector()
    {
        return $this->belongsTo(Sector::class);
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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sector_id')->constrained('sectors')->onDelete('cascade');
            $table->integer('numero');
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lotes');
    }
};
```

---

## Paso 4: Ejecutar la migración

Para aplicar la migración y crear la tabla `lotes`, ejecutar en la terminal:

```sh
php artisan migrate
```
