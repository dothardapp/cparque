# Creación del Modelo Cliente en Laravel con Migración

Este documento registra los pasos para la creación del modelo `Cliente` en el proyecto `cparque` con Laravel.

## Paso 1: Crear el modelo y la migración

Ejecutar en la terminal:

```sh
php artisan make:model Cliente -m
```

Este comando genera:

-   El modelo en `app/Models/Cliente.php`
-   La migración en `database/migrations/`

---

## Paso 2: Definir el modelo `Cliente`

Editar el archivo `app/Models/Cliente.php` para agregar los atributos `fillable`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'dni',
        'codigo',
        'nombre',
        'apellido',
        'fecha_nacimiento',
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
```

---

## Paso 4: Ejecutar la migración

Para aplicar la migración y crear la tabla `clientes`, ejecutar en la terminal:

```sh
php artisan migrate
```
