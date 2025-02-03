# Creación del Modelo Ctacte en Laravel con Migración

Este documento registra los pasos para la creación del modelo `Ctacte` en el proyecto `cparque` con Laravel.

## Descripción

-   `Ctacte` representa los movimientos de cuenta corriente de un cliente.
-   Relación con `Cliente`: cada movimiento pertenece a un cliente.
-   `tipo` puede representar el tipo de movimiento (ej.: pago, deuda, ajuste, etc.).
-   `debe` y `haber` representan montos en la cuenta corriente.
-   `anio` y `mes` permiten identificar el período del movimiento.
-   `estado` puede indicar si el movimiento está **pendiente, pagado, cancelado, etc.**.
-   `recibo` almacena el número de comprobante asociado.

---

## Paso 1: Crear el modelo y la migración

Ejecutar en la terminal:

```sh
php artisan make:model Ctacte -m
```

Este comando genera:

-   El modelo en `app/Models/Ctacte.php`
-   La migración en `database/migrations/`

---

## Paso 2: Definir el modelo `Ctacte`

Editar el archivo `app/Models/Ctacte.php` para agregar los atributos `fillable` y la relación con `Cliente`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ctacte extends Model
{
    use HasFactory;

    protected $table = 'ctasctes';

    protected $fillable = [
        'cliente_id',
        'fecha',
        'tipo',
        'debe',
        'haber',
        'anio',
        'mes',
        'estado',
        'recibo',
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
        Schema::create('ctasctes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->date('fecha');
            $table->string('tipo', 50);
            $table->decimal('debe', 10, 2)->default(0);
            $table->decimal('haber', 10, 2)->default(0);
            $table->integer('anio');
            $table->integer('mes');
            $table->string('estado', 50);
            $table->string('recibo', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ctasctes');
    }
};
```

---

## Paso 4: Ejecutar la migración

Para aplicar la migración y crear la tabla `ctasctes`, ejecutar en la terminal:

```sh
php artisan migrate
```
