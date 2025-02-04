# Script en PHP para migrar cuentas corrientes a la tabla `ctasctes`

Este documento describe el proceso de migración de los registros de cuentas corrientes desde `CPARQUEbase.CtaCte` hacia la tabla `ctasctes` en `cparque`, asegurando la correcta asignación de los clientes mediante el campo `codigo`.

---

## Paso 1: Descripción del proceso

Este script:

-   Recorre la tabla `CtaCte` de `CPARQUEbase`.
-   Obtiene el **cliente_id** a partir del campo `codigo` en la tabla `clientes` de `cparque`.
-   Si el `cliente_id` no se encuentra, asigna el cliente **auxiliar (ID 185000)**.
-   Inserta los registros en la tabla `ctasctes` con las siguientes validaciones:
    -   `fecha` → Si es `NULL`, se establece en `'1970-01-01'`.
    -   `tipo`, `debe`, `haber`, `anio`, `mes`, `estado`, `recibo` → Se asignan valores por defecto si son `NULL`.

---

## Paso 2: Crear el script en PHP

Guardar el siguiente código en un archivo llamado `migrar_ctasctes.php`:

```php
<?php

$host = "localhost";
$dbname_old = "CPARQUEbase";
$dbname_new = "cparque";
$username = "root";
$password = "joselote"; // Cambia esto por la contraseña correcta

try {
    // Conexión a ambas bases de datos
    $pdo_old = new PDO("mysql:host=$host;dbname=$dbname_old;charset=utf8mb4", $username, $password);
    $pdo_new = new PDO("mysql:host=$host;dbname=$dbname_new;charset=utf8mb4", $username, $password);

    $pdo_old->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_new->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión establecida correctamente.\n";

    // Obtener registros de la tabla antigua
    $stmt_old = $pdo_old->query("SELECT * FROM CtaCte");
    $registros = $stmt_old->fetchAll(PDO::FETCH_ASSOC);

    echo "Total de registros a migrar: " . count($registros) . PHP_EOL;

    // Preparar la consulta de inserción en la nueva base de datos
    $insert_stmt = $pdo_new->prepare("
        INSERT INTO ctasctes (cliente_id, fecha, tipo, debe, haber, anio, mes, estado, recibo, created_at, updated_at)
        VALUES (:cliente_id, :fecha, :tipo, :debe, :haber, :anio, :mes, :estado, :recibo, NOW(), NOW())
    ");

    $error_count = 0;
    foreach ($registros as $index => $row) {
        try {
            // Obtener cliente_id a partir del código
            $stmt_cliente = $pdo_new->prepare("SELECT id FROM clientes WHERE codigo = :codigo LIMIT 1");
            $stmt_cliente->execute([':codigo' => $row['codigo']]);
            $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
            $cliente_id = $cliente ? $cliente['id'] : 185000; // Cliente auxiliar si no existe

            // Preparar datos para inserción con valores por defecto
            $params = [
                'cliente_id' => $cliente_id,
                'fecha' => !empty($row['fecha']) ? $row['fecha'] : '1970-01-01',
                'tipo' => !empty($row['tipo']) ? $row['tipo'] : 'Desconocido',
                'debe' => is_numeric($row['debe']) ? $row['debe'] : 0.00,
                'haber' => is_numeric($row['haber']) ? $row['haber'] : 0.00,
                'anio' => !empty($row['ano']) ? $row['ano'] : date('Y'),
                'mes' => !empty($row['mes']) ? $row['mes'] : 1,
                'estado' => !empty($row['estado']) ? $row['estado'] : 'Pendiente',
                'recibo' => !empty($row['recibo']) ? $row['recibo'] : NULL
            ];

            // Ejecutar inserción
            $insert_stmt->execute($params);

            echo "[$index] Insertado: Cliente ID {$cliente_id}, Fecha {$params['fecha']}, Tipo {$params['tipo']}, Debe {$params['debe']}, Haber {$params['haber']}" . PHP_EOL;

        } catch (Exception $e) {
            $error_count++;
            echo "❌ Error en la inserción del registro $index (Cliente ID {$cliente_id}): " . $e->getMessage() . PHP_EOL;
        }
    }

    echo "✅ Migración completada con $error_count errores." . PHP_EOL;

} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
```

---

## Paso 3: Ejecutar el script en PHP

Para ejecutar la **migración de cuentas corrientes**, usa:

```sh
php migrar_ctasctes.php
```

---

## Paso 4: Mensajes esperados en la terminal

-   "Conexión establecida correctamente."
-   "Total de registros a migrar: X"
-   "[X] Insertado: Cliente ID X, Fecha X, Tipo X, Debe X, Haber X"
-   "❌ Error en la inserción del registro X (Cliente ID X): error SQL..."
-   "✅ Migración completada con X errores."

---

## Paso 5: Validaciones y consideraciones

✅ **Si el cliente no existe, se asigna al cliente auxiliar `185000`**  
✅ **Los valores `NULL` en `fecha`, `tipo`, `debe`, `haber`, `anio`, `mes`, `estado`, `recibo` se reemplazan con valores por defecto**  
✅ **Si hay errores de integridad, los registros problemáticos se listarán sin detener la ejecución**  
✅ **Todos los movimientos quedan registrados en la terminal para seguimiento**

🚀 ¡Listo para ejecutar y subir a Git! Avísame si necesitas ajustes.
