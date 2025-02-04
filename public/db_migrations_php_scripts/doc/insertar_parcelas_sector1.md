# Script en PHP para insertar parcelas en el Sector 1

Este documento describe el proceso de inserción de parcelas en la tabla `parcelas` en la base de datos `cparque`, asegurando que no se creen registros duplicados y que cada parcela pertenezca al lote y sector correctos.

---

## Paso 1: Descripción del proceso

Este script en PHP inserta parcelas en la tabla `parcelas`, basándose en la distribución de lotes y cantidades de parcelas especificadas para el **Sector 1**.

**Reglas de transformación y validación:**

-   `lote_id (BIGINT)` → Se obtiene buscando el `id` de la tabla `lotes` basado en el `sector_id` y `numero` del lote.
-   `numero (INT)` → Se asigna desde `1` hasta el límite de parcelas para ese lote.
-   `estado (ENUM)` → Se establece por defecto en `"libre"`.
-   `descripcion (VARCHAR(255))` → Se genera automáticamente como `"Parcela X, Lote Y, Sector Z"`.
-   `cliente_id (BIGINT)` → Se asigna el valor predeterminado `185000` (Parque Zenta S.R.L.).
-   `created_at` y `updated_at` → Se establecen con la fecha y hora actual.

---

## Paso 2: Distribución de parcelas en el Sector 1

| Lote | Parcelas |
| ---- | -------- |
| 1    | 48       |
| 2    | 64       |
| 3    | 64       |
| 4    | 64       |
| 5    | 64       |
| 6    | 48       |
| 7    | 48       |
| 8    | 48       |
| 9    | 32       |
| 10   | 32       |
| 11   | 32       |
| 12   | 16       |
| 13   | 16       |
| 14   | 16       |
| 15   | 12       |
| 16   | 8        |

---

## Paso 3: Crear el script en PHP

Guardar el siguiente código en un archivo llamado `insertar_parcelas_sector1.php`:

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

    // Definir los lotes y su cantidad de parcelas en el Sector 1
    $lotes_parcelas = [
        1 => 48, 2 => 64, 3 => 64, 4 => 64, 5 => 64,
        6 => 48, 7 => 48, 8 => 48, 9 => 32, 10 => 32, 11 => 32,
        12 => 16, 13 => 16, 14 => 16, 15 => 12, 16 => 8
    ];

    // Sector en el que se insertarán las parcelas
    $sector_id = 1;
    $cliente_id_predeterminado = 185000;

    // Preparar la consulta de inserción con verificación previa
    $query = "INSERT INTO parcelas (lote_id, numero, estado, descripcion, cliente_id, created_at, updated_at)
              SELECT :lote_id, :numero, 'libre', :descripcion, :cliente_id, NOW(), NOW()
              WHERE NOT EXISTS (
                  SELECT 1 FROM parcelas WHERE lote_id = :lote_id AND numero = :numero
              )";

    $stmt = $pdo->prepare($query);
    $count = 0;

    foreach ($lotes_parcelas as $lote => $cantidad_parcelas) {
        // Obtener el lote_id desde la tabla lotes
        $lote_query = $pdo->prepare("SELECT id FROM lotes WHERE sector_id = :sector_id AND numero = :numero");
        $lote_query->execute([':sector_id' => $sector_id, ':numero' => $lote]);
        $lote_id = $lote_query->fetchColumn();

        if (!$lote_id) {
            echo "Advertencia: No se encontró lote {$lote} en sector {$sector_id}, omitiendo inserción de parcelas.\n";
            continue;
        }

        for ($parcela = 1; $parcela <= $cantidad_parcelas; $parcela++) {
            $descripcion = "Parcela $parcela, Lote $lote, Sector $sector_id";

            $stmt->execute([
                ':lote_id' => $lote_id,
                ':numero' => $parcela,
                ':descripcion' => $descripcion,
                ':cliente_id' => $cliente_id_predeterminado
            ]);

            if ($stmt->rowCount() > 0) {
                echo "Parcela insertada: Lote {$lote}, Número {$parcela}, Descripción: {$descripcion}, Cliente ID: {$cliente_id_predeterminado}\n";
                $count++;
            } else {
                echo "Parcela {$parcela} en Lote {$lote} ya existe, no se insertó.\n";
            }
        }
    }

    echo "Proceso completado. Total de parcelas insertadas: $count\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
```

---

## Paso 4: Ejecutar el script en PHP

Para ejecutar el script de inserción de parcelas en el **Sector 1**, sigue estos pasos:

1. Guarda el código anterior en un archivo llamado `insertar_parcelas_sector1.php`.
2. Asegúrate de que PHP esté instalado en tu sistema.
3. En la terminal, navega a la carpeta donde guardaste el archivo.
4. Ejecuta el siguiente comando:

    ```sh
    php insertar_parcelas_sector1.php
    ```

---

## Paso 5: Mensajes esperados en la terminal

-   "Conexión establecida correctamente."
-   "Parcela insertada: Lote X, Número Y, Descripción: Parcela Y, Lote X, Sector 1, Cliente ID: 185000"
-   "Parcela Y en Lote X ya existe, no se insertó."
-   "Advertencia: No se encontró lote X en sector 1, omitiendo inserción de parcelas."
-   "Proceso completado. Total de parcelas insertadas: X."

Si hay errores, el script los mostrará en pantalla.
