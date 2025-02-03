# Creación del Modelo Inhumado en Laravel con Migración

Este documento registra los pasos para la creación del modelo `Inhumado` en el proyecto `cparque` con Laravel.

## Descripción

-   Un `Inhumado` representa a una persona enterrada en una `Parcela`.
-   Una `Parcela` puede contener hasta **tres inhumados**, organizados por `nivel`:
    -   **Primer nivel**: el más profundo.
    -   **Segundo nivel**: nivel intermedio.
    -   **Tercer nivel**: nivel más superficial.
-   `nivel` será un campo tipo `ENUM` con los valores: `'primer_nivel', 'segundo_nivel', 'tercer_nivel'`.
-   `fecha_nacimiento` tendrá un valor por defecto de `'1970-01-01'`.

---

## Paso 1: Crear el modelo y la migración

Ejecutar en la terminal:

```sh
php artisan make:model Inhumado -m
```

Este comando genera:

-   El modelo en `app/Models/Inhumado.php`
-   La migración en `database/migrations/`

---

## Paso 2: Definir el modelo `Inhumado`

Editar el archivo `app/Models/Inhumado.php` para agregar los atributos `fillable` y las relaciones con `Cliente` y `Parcela`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inhumado extends Model
{
    use HasFactory;

    protected $table = 'inhumados';

    protected $fillable = [
        'cliente_id',
        'parcela_id',
        'nivel',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'fecha_inhumacion',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function parcela()
    {
        return $this->belongsTo(Parcela::class);
    }
}
```

---

## Paso 3: Editar la migración

Ubicar el archivo en `database/migrations/` y editarlo con la siguiente estructura:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
```

---

## Paso 4: Ejecutar la migración

Para aplicar la migración y crear la tabla `inhumados`, ejecutar en la terminal:

```sh
php artisan migrate
```
