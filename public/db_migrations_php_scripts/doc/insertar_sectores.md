# Script en PHP para insertar sectores en la tabla `sectors`

Este documento describe el proceso de inserción de sectores en la tabla `sectors` en la base de datos `cparque`, asegurando que no se creen registros duplicados.

---

## Paso 1: Descripción del proceso

Este script en PHP inserta los sectores numerados del `1` al `8` en la tabla `sectors`. Antes de insertar cada sector, verifica si ya existe en la base de datos para evitar duplicados.

**Transformaciones y reglas:**

-   `numero (INT)` → Se inserta con valores del `1` al `8`.
-   `descripcion (VARCHAR(255))` → Se genera automáticamente como `"Sector X"`, donde `X` es el número del sector.
-   `created_at` y `updated_at` → Se establecen con la fecha y hora actual.
-   Se omiten los sectores que ya existen (`numero` único).

---

## Paso 2: Crear el script en PHP

Guardar el siguiente código en un archivo llamado `insertar_sectores.php`:

```php
<?php

$host = 'localhost';
$user = 'root';
$password = 'joselote';
$database = 'cparque';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión establecida correctamente.\n";

    // Preparar la consulta de inserción con verificación previa
    $query = "INSERT INTO sectors (numero, descripcion, created_at, updated_at)
              SELECT :numero, :descripcion, NOW(), NOW()
              WHERE NOT EXISTS (SELECT 1 FROM sectors WHERE numero = :numero)";

    $stmt = $pdo->prepare($query);
    $count = 0;

    for ($i = 1; $i <= 8; $i++) {
        $descripcion = "Sector $i";

        $stmt->execute([
            ':numero' => $i,
            ':descripcion' => $descripcion
        ]);

        if ($stmt->rowCount() > 0) {
            echo "Sector insertado: Número {$i}, Descripción: {$descripcion}\n";
            $count++;
        } else {
            echo "Sector {$i} ya existe, no se insertó.\n";
        }
    }

    echo "Proceso completado. Total de sectores insertados: $count\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
```

---

## Paso 3: Ejecutar el script en PHP

Para ejecutar el script de inserción de sectores, sigue estos pasos:

1. Guarda el código anterior en un archivo llamado `insertar_sectores.php`.
2. Asegúrate de que PHP esté instalado en tu sistema.
3. En la terminal, navega a la carpeta donde guardaste el archivo.
4. Ejecuta el siguiente comando:

    ```sh
    php insertar_sectores.php
    ```

---

## Paso 4: Mensajes esperados en la terminal

-   "Conexión establecida correctamente."
-   "Sector insertado: Número X, Descripción: Sector X"
-   "Sector X ya existe, no se insertó."
-   "Proceso completado. Total de sectores insertados: X."

Si hay errores, el script los mostrará en pantalla.
