# Script en PHP para insertar parcelas en el Sector 3

Este documento describe el proceso de inserción de parcelas en la tabla `parcelas` en la base de datos `cparque`, asegurando que no se creen registros duplicados y que cada parcela pertenezca al lote y sector correctos.

---

## Paso 1: Descripción del proceso

Este script en PHP inserta parcelas en la tabla `parcelas`, basándose en la distribución de lotes y cantidades de parcelas especificadas para el **Sector 3**.

**Reglas de transformación y validación:**

-   `lote_id (BIGINT)` → Se obtiene buscando el `id` de la tabla `lotes` basado en el `sector_id` y `numero` del lote.
-   `numero (INT)` → Se asigna desde el número de parcela inicial hasta el número de parcela final por lote.
-   `estado (ENUM)` → Se establece por defecto en `"libre"`.
-   `descripcion (VARCHAR(255))` → Se genera automáticamente como `"Parcela X, Lote Y, Sector Z"`.
-   `cliente_id (BIGINT)` → Se asigna el valor predeterminado `185000` (Parque Zenta S.R.L.).
-   `created_at` y `updated_at` → Se establecen con la fecha y hora actual.

---

## Paso 2: Distribución de parcelas en el Sector 3

| Lote | Parcelas (Inicio - Fin) |
| ---- | ----------------------- |
| 1    | 1 - 51                  |
| 2    | 52 - 173                |
| 3    | 174 - 295               |
| 4    | 296 - 417               |
| 5    | 418 - 539               |
| 6    | 540 - 661               |
| 7    | 662 - 783               |
| 8    | 784 - 905               |
| 9    | 906 - 1027              |
| 10   | 1028 - 1149             |
| 11   | 1150 - 1271             |
| 12   | 1272 - 1393             |
| 13   | 1394 - 1515             |
| 14   | 1516 - 1637             |
| 15   | 1638 - 1759             |
| 16   | 1760 - 1881             |
| 17   | 1882 - 2003             |
| 18   | 2004 - 2054             |

---

## Paso 3: Crear el script en PHP

Guardar el siguiente código en un archivo llamado `insertar_parcelas_sector3.php`:

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

    // Verificar si el cliente 185000 existe y crearlo si no
    $cliente_id_predeterminado = 185000;

    $check_cliente_query = "SELECT id FROM clientes WHERE id = :cliente_id";
    $check_stmt = $pdo->prepare($check_cliente_query);
    $check_stmt->execute([':cliente_id' => $cliente_id_predeterminado]);

    if (!$check_stmt->fetchColumn()) {
        echo "El cliente 185000 no existe. Creándolo...\n";
        $insert_cliente_query = "INSERT INTO clientes (id, dni, codigo, nombre, apellido, fecha_nacimiento, created_at, updated_at)
                                 VALUES (185000, '00000000', '185000', 'Parque Zenta S.R.L.', 'Predeterminado', '2000-01-01', NOW(), NOW())";
        $pdo->exec($insert_cliente_query);
        echo "Cliente 185000 creado con éxito.\n";
    }

    // Definir los lotes y su rango de parcelas en el Sector 3
    $lotes_parcelas = [
        1 => [1, 51], 2 => [52, 173], 3 => [174, 295], 4 => [296, 417], 5 => [418, 539],
        6 => [540, 661], 7 => [662, 783], 8 => [784, 905], 9 => [906, 1027], 10 => [1028, 1149],
        11 => [1150, 1271], 12 => [1272, 1393], 13 => [1394, 1515], 14 => [1516, 1637],
        15 => [1638, 1759], 16 => [1760, 1881], 17 => [1882, 2003], 18 => [2004, 2054]
    ];

    // Sector en el que se insertarán las parcelas
    $sector_id = 3;

    // Preparar la consulta de inserción con verificación previa
    $query = "INSERT INTO parcelas (lote_id, numero, estado, descripcion, cliente_id, created_at, updated_at)
              SELECT :lote_id, :numero, 'libre', :descripcion, :cliente_id, NOW(), NOW()
              WHERE NOT EXISTS (
                  SELECT 1 FROM parcelas WHERE lote_id = :lote_id AND numero = :numero
              )";

    $stmt = $pdo->prepare($query);
    $count = 0;

    foreach ($lotes_parcelas as $lote => [$inicio, $fin]) {
        // Obtener el lote_id desde la tabla lotes
        $lote_query = $pdo->prepare("SELECT id FROM lotes WHERE sector_id = :sector_id AND numero = :numero");
        $lote_query->execute([':sector_id' => $sector_id, ':numero' => $lote]);
        $lote_id = $lote_query->fetchColumn();

        if (!$lote_id) {
            echo "Advertencia: No se encontró lote {$lote} en sector {$sector_id}, omitiendo inserción de parcelas.\n";
            continue;
        }

        for ($parcela = $inicio; $parcela <= $fin; $parcela++) {
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

Para ejecutar el script de inserción de parcelas en el **Sector 3**, sigue estos pasos:

1. Guarda el código anterior en un archivo llamado `insertar_parcelas_sector3.php`.
2. Asegúrate de que PHP esté instalado en tu sistema.
3. En la terminal, navega a la carpeta donde guardaste el archivo.
4. Ejecuta el siguiente comando:

    ```sh
    php insertar_parcelas_sector3.php
    ```

---

## Paso 5: Mensajes esperados en la terminal

-   "Conexión establecida correctamente."
-   "Parcela insertada: Lote X, Número Y, Descripción: Parcela Y, Lote X, Sector 3, Cliente ID: 185000"
-   "Parcela Y en Lote X ya existe, no se insertó."
-   "Advertencia: No se encontró lote X en sector 3, omitiendo inserción de parcelas."
-   "Proceso completado. Total de parcelas insertadas: X."

Si hay errores, el script los mostrará en pantalla.
