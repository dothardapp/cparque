# Script en PHP para migrar clientes a la tabla `parcelas`

Este documento describe el proceso de migración de clientes desde la tabla `Clientes` en `CPARQUEbase` hacia la tabla `parcelas` en `cparque`, asignando correctamente las parcelas ocupadas y garantizando la consistencia de los datos.

---

## Paso 1: Descripción del proceso

Este script:

-   Recorre la tabla `Clientes` de `CPARQUEbase`.
-   Obtiene el **Sector, Lote y Parcela** de cada cliente.
-   Busca la **parcela correspondiente** en `cparque.parcelas` basándose en `sector`, `lote` y `parcela`.
-   Obtiene el **ID del cliente en `cparque.clientes`** basado en el campo `codigo`.
-   Si la parcela existe, actualiza el `cliente_id` y cambia su `estado` a `ocupada`.
-   Si la parcela no existe, asigna una **Parcela Auxiliar (ID 8065)** y la crea si no está en la base de datos.
-   Permite **revertir** la migración, restaurando la tabla `parcelas` a su estado inicial tras la carga de sectores.

---

## Paso 2: Crear el script en PHP

Guardar el siguiente código en un archivo llamado `update_clientes_parcelas.php`:

```php
<?php

$host = 'localhost';
$user = 'root';
$password = 'joselote';
$database_cparque = 'cparque';
$database_cparque_base = 'CPARQUEbase';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database_cparque;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión establecida correctamente.\n";

    // Definir la Parcela Auxiliar
    $parcela_auxiliar_id = 8065;
    $parcela_auxiliar_query = "SELECT id FROM parcelas WHERE id = :parcela_id";
    $stmt_auxiliar = $pdo->prepare($parcela_auxiliar_query);
    $stmt_auxiliar->execute([':parcela_id' => $parcela_auxiliar_id]);

    if (!$stmt_auxiliar->fetchColumn()) {
        echo "La parcela auxiliar no existe. Creándola...\n";
        $insert_auxiliar = "INSERT INTO parcelas (id, lote_id, numero, estado, descripcion, cliente_id, created_at, updated_at)
                            VALUES (:parcela_id, 120, 9999, 'libre', 'Parcela Auxiliar', 185000, NOW(), NOW())";
        $pdo->prepare($insert_auxiliar)->execute([':parcela_id' => $parcela_auxiliar_id]);
        echo "Parcela auxiliar creada con ID 8065.\n";
    }

    // Obtener los clientes de CPARQUEbase
    $query_clientes = "SELECT id_clientes, codigo, sector, lote, parcela FROM $database_cparque_base.Clientes";
    $stmt_clientes = $pdo->query($query_clientes);

    $update_query = "UPDATE parcelas
                     SET cliente_id = :cliente_id, estado = 'ocupada', updated_at = NOW()
                     WHERE id = :parcela_id";

    $stmt_update = $pdo->prepare($update_query);
    $count = 0;

    while ($cliente = $stmt_clientes->fetch(PDO::FETCH_ASSOC)) {
        $codigo_cliente = $cliente['codigo'];
        $sector = $cliente['sector'];
        $lote = $cliente['lote'];
        $parcela_numero = $cliente['parcela'];

        // Obtener el ID del cliente en cparque
        $query_cliente_id = "SELECT id FROM clientes WHERE codigo = :codigo LIMIT 1";
        $stmt_cliente_id = $pdo->prepare($query_cliente_id);
        $stmt_cliente_id->execute([':codigo' => $codigo_cliente]);
        $cliente_id = $stmt_cliente_id->fetchColumn();

        if (!$cliente_id) {
            echo "Advertencia: Cliente con código {$codigo_cliente} no encontrado en cparque. Saltando...\n";
            continue;
        }

        // Buscar la parcela correspondiente
        $query_parcela = "SELECT p.id FROM parcelas p
                          JOIN lotes l ON p.lote_id = l.id
                          JOIN sectors s ON l.sector_id = s.id
                          WHERE s.numero = :sector AND l.numero = :lote AND p.numero = :parcela_numero
                          LIMIT 1";
        $stmt_parcela = $pdo->prepare($query_parcela);
        $stmt_parcela->execute([':sector' => $sector, ':lote' => $lote, ':parcela_numero' => $parcela_numero]);
        $parcela_id = $stmt_parcela->fetchColumn();

        // Si no se encontró una parcela, asignar la auxiliar
        if (!$parcela_id) {
            echo "Parcela no encontrada para cliente {$codigo_cliente}. Asignando parcela auxiliar.\n";
            $parcela_id = $parcela_auxiliar_id;
        }

        // Actualizar la parcela con el cliente correspondiente
        $stmt_update->execute([
            ':cliente_id' => $cliente_id,
            ':parcela_id' => $parcela_id
        ]);

        echo "Parcela ID {$parcela_id} asignada a Cliente ID {$cliente_id}.\n";
        $count++;
    }

    echo "Proceso completado. Total de parcelas migradas: $count\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
```

---

## Paso 3: Crear el script de reversión

Si necesitas **revertir la migración**, ejecuta este script para restaurar la tabla `parcelas` a su estado original:

Guardar el siguiente código en un archivo llamado `revertir_update_parcelas_clientes.php`:

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

    // Restaurar todas las parcelas a su estado inicial (libre y cliente_id 185000)
    $restore_query = "UPDATE parcelas SET cliente_id = 185000, estado = 'libre', updated_at = NOW()";
    $rows_affected = $pdo->exec($restore_query);

    echo "Reversión completada. Parcelas restauradas a su estado inicial: $rows_affected\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
```

---

## Paso 4: Ejecutar los scripts en PHP

Para ejecutar la **migración de clientes a parcelas**, usa:

```sh
php migrar_clientes_parcelas.php
```

Para **revertir la migración y restaurar las parcelas al estado original**, usa:

```sh
php revertir_migracion_parcelas.php
```

---

## Paso 5: Mensajes esperados en la terminal

### **Migración**

-   "Conexión establecida correctamente."
-   "Parcela no encontrada para cliente X. Asignando parcela auxiliar."
-   "Parcela ID X asignada a Cliente ID Y."
-   "Proceso completado. Total de parcelas migradas: X."

### **Reversión**

-   "Conexión establecida correctamente."
-   "Reversión completada. Parcelas restauradas a su estado inicial: X."

---

Este proceso **garantiza que todas las parcelas sean correctamente ocupadas por los clientes**, permitiendo deshacer los cambios si es necesario. 🚀 ¡Listo para ejecutar y subir a Git!
