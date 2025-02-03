# Creación del Modelo Contacto en Laravel con Migración

Este documento registra los pasos para la creación del modelo `Contacto` en el proyecto `cparque` con Laravel.

## Descripción

-   `Contacto` permite registrar múltiples datos de contacto para un `Cliente`.
-   Se pueden almacenar diferentes tipos de contacto:
    -   **Domicilios**
    -   **Teléfonos**
    -   **Correos electrónicos**
-   `comentario` permite agregar información adicional, por ejemplo, si es una dirección laboral.
-   `principal` permite marcar un dato como el principal del cliente.

---

## Paso 1: Crear el modelo y la migración

Ejecutar en la terminal:

```sh
php artisan make:model Contacto -m
```

Este comando genera:

-   El modelo en `app/Models/Contacto.php`
-   La migración en `database/migrations/`

---

## Paso 2: Definir el modelo `Contacto`

Editar el archivo `app/Models/Contacto.php` para agregar los atributos `fillable` y la relación con `Cliente`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    use HasFactory;

    protected $table = 'contactos';

    protected $fillable = [
        'cliente_id',
        'tipo',
        'valor',
        'comentario',
        'principal',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
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
```

---

## Paso 4: Ejecutar la migración

Para aplicar la migración y crear la tabla `contactos`, ejecutar en la terminal:

```sh
php artisan migrate
```
