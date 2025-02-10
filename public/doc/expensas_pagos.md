# Documentación de Migraciones y Modelos de Expensas, Pagos y Servicios

## 1️⃣ Creación de nuevos modelos y migraciones
Ejecuta los siguientes comandos para generar los modelos y migraciones:
```bash
php artisan make:model Expensa -m
php artisan make:model Pago -m
php artisan make:model ServicioAdicional -m
php artisan make:model VentaServicio -m
```

---

## 2️⃣ Contenido de las migraciones

### `create_expensas_table.php`
```php
Schema::create('expensas', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('parcela_id');
    $table->unsignedBigInteger('cliente_id');
    $table->integer('anio');
    $table->integer('mes');
    $table->decimal('monto', 10, 2);
    $table->decimal('saldo', 10, 2)->default(0);
    $table->enum('estado', ['pendiente', 'pagado parcialmente', 'pagado'])->default('pendiente');
    $table->unsignedBigInteger('user_id');
    $table->timestamps();

    $table->unique(['parcela_id', 'anio', 'mes']);
    $table->foreign('parcela_id')->references('id')->on('parcelas')->onDelete('cascade')->onUpdate('cascade');
    $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade')->onUpdate('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
});
```

---

## 3️⃣ Contenido de los Modelos
Los modelos se han actualizado con las relaciones correctas:

### `Expensa.php`
```php
public function parcela() {
    return $this->belongsTo(Parcela::class);
}
public function cliente() {
    return $this->belongsTo(Cliente::class);
}
public function usuario() {
    return $this->belongsTo(User::class, 'user_id');
}
public function pagos() {
    return $this->hasMany(Pago::class);
}
```

### `Parcela.php`
```php
public function cliente() {
    return $this->belongsTo(Cliente::class);
}
public function expensas() {
    return $this->hasMany(Expensa::class);
}
```

---

## 4️⃣ Ejecución del Script de Migración de Datos
Para migrar los datos de la base de datos antigua, ejecuta el script PHP de migración de expensas.

---

## 5️⃣ Resumen de Mejoras
✅ Se incluyó `cliente_id` en `expensas` para evitar `JOIN` adicionales.
✅ Se mejoraron los modelos con relaciones más eficientes.
✅ Se optimizó el script de migración para reducir errores duplicados.
✅ Se documentó cómo aplicar y verificar cada cambio en la base de datos.

🚀 **Sistema listo para operar!** 🚀
