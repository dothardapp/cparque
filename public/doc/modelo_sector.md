# Creación del Modelo Sector en Laravel con Migración

Este documento registra los pasos para la creación del modelo `Sector` en el proyecto `cparque` con Laravel.

## Paso 1: Crear el modelo y la migración

Ejecutar en la terminal:

```sh
php artisan make:model Sector -m
```

Este comando genera:

-   El modelo en `app/Models/Sector.php`
-   La migración en `database/migrations/`

---

## Paso 2: Definir el modelo `Sector`

Editar el archivo `app/Models/Sector.php` para agregar los atributos `fillable`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    protected $table = 'sectors';

    protected $fillable = [
        'numero',
        'descripcion',
    ];
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
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->integer('numero')->unique();
            $table->string('descripcion', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sectors');
    }
};
```

---

## Paso 4: Ejecutar la migración

Para aplicar la migración y crear la tabla `sectors`, ejecutar en la terminal:

```sh
php artisan migrate
```
