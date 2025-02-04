# Script en PHP para insertar lotes en la tabla `lotes`

Este documento describe el proceso de inserción de lotes en la tabla `lotes` en la base de datos `cparque`, asegurando que no se creen registros duplicados.

---

## Paso 1: Descripción del proceso

Este script en PHP inserta lotes en la tabla `lotes`, basándose en la cantidad de lotes asignados por sector. Cada lote se asocia a un `sector_id`, y la cantidad de lotes por sector se define de la siguiente manera:

| Sector | Cantidad de Lotes |
| ------ | ----------------- |
| 1      | 16                |
| 2      | 15                |
| 3      | 18                |
| 4      | 18                |
| 5      | 12                |
| 6      | 12                |
| 7      | 14                |
| 8      | 14                |

**Reglas de transformación y validación:**

-   `sector_id (BIGINT)` → Se obtiene buscando el `id` de la tabla `sectors` basado en el número del sector.
-   `numero (INT)` → Se asigna desde `1` hasta la cantidad de lotes definida por sector.
-   `descripcion (VARCHAR(255))` → Se genera automáticamente como `"Lote X en sector Y"`, donde `X` es el número de lote y `Y` es el número de sector.
-   `created_at` y `updated_at` → Se establecen con la fecha y hora actual.
-   Se omiten los lotes que ya existen (`sector_id` y `numero` únicos).

---

## Paso 2: Crear el script en PHP

Guardar el siguiente código en un archivo llamado `insertar_lotes.php`:

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

    // Definir la cantidad de lotes por sector
    $sectores_lotes = [
        1 => 16,
        2 => 15,
        3 => 18,
        4 => 18,
        5 => 12,
        6 => 12,
        7 => 14,
        8 => 14
    ];

    // Preparar la consulta de inserción con verificación previa
    $query = "INSERT INTO lotes (sector_id, numero, descripcion, created_at, updated_at)
              SELECT :sector_id, :numero, :descripcion, NOW(), NOW()
              WHERE NOT EXISTS (
                  SELECT 1 FROM lotes WHERE sector_id = :sector_id AND numero = :numero
              )";

    $stmt = $pdo->prepare($query);
    $count = 0;

    foreach ($sectores_lotes as $sector => $cantidad_lotes) {
        // Obtener el sector_id desde la tabla sectors
        $sector_query = $pdo->prepare("SELECT id FROM sectors WHERE numero = :numero");
        $sector_query->execute([':numero' => $sector]);
        $sector_id = $sector_query->fetchColumn();

        if (!$sector_id) {
            echo "Advertencia: No se encontró sector con número {$sector}, omitiendo inserción de lotes.\n";
            continue;
        }

        for ($lote = 1; $lote <= $cantidad_lotes; $lote++) {
            $descripcion = "Lote $lote en sector $sector";

            $stmt->execute([
                ':sector_id' => $sector_id,
                ':numero' => $lote,
                ':descripcion' => $descripcion
            ]);

            if ($stmt->rowCount() > 0) {
                echo "Lote insertado: Sector {$sector}, Número {$lote}, Descripción: {$descripcion}\n";
                $count++;
            } else {
                echo "Lote {$lote} en sector {$sector} ya existe, no se insertó.\n";
            }
        }
    }

    echo "Proceso completado. Total de lotes insertados: $count\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
```

---

## Paso 3: Ejecutar el script en PHP

Para ejecutar el script de inserción de lotes, sigue estos pasos:

1. Guarda el código anterior en un archivo llamado `insertar_lotes.php`.
2. Asegúrate de que PHP esté instalado en tu sistema.
3. En la terminal, navega a la carpeta donde guardaste el archivo.
4. Ejecuta el siguiente comando:

    ```sh
    php insertar_lotes.php
    ```

---

## Paso 4: Mensajes esperados en la terminal

-   "Conexión establecida correctamente."
-   "Lote insertado: Sector X, Número Y, Descripción: Lote Y en sector X"
-   "Lote Y en sector X ya existe, no se insertó."
-   "Advertencia: No se encontró sector con número X, omitiendo inserción de lotes."
-   "Proceso completado. Total de lotes insertados: X."

Si hay errores, el script los mostrará en pantalla.
