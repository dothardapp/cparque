# Migración de datos de contacto de `Clientes` en `CPARQUEbase` a `contactos` en `cparque`

Este documento describe el proceso de migración de los datos de contacto de la tabla `Clientes` en la base de datos `CPARQUEbase` a la tabla `contactos` en `cparque`, asignando `cliente_id` basado en el `codigo`.

---

## Paso 1: Transformaciones de datos

Se aplican las siguientes transformaciones:

-   `id_clientes` → No se usa directamente, se asocia con `cliente_id` en `contactos` a través del `codigo`.
-   `barrio (CHAR(40))` → Se inserta en `contactos.barrio` si no está vacío.
-   `domicilio (CHAR(80))` → Se inserta en `contactos.domicilio` si no está vacío.
-   `telefono (CHAR(40))` → Se inserta en `contactos.telefono` si no está vacío.
-   `email` → No existe en `CPARQUEbase`, se inserta como `NULL`.
-   `comentario` → Se inserta como `NULL`.
-   `principal` → Se establece en `1` para todos los registros.
-   `created_at` y `updated_at` → Se establecen con la fecha y hora actual.

---

## Paso 2: Crear el script de migración

Guardar el siguiente código en un archivo llamado `migrar_contactos.php`:

```php
<?php

$host = 'localhost';
$user = 'root';
$password = 'joselote';
$database_old = 'CPARQUEbase';
$database_new = 'cparque';

try {
    $pdo_old = new PDO("mysql:host=$host;dbname=$database_old;charset=utf8mb4", $user, $password);
    $pdo_new = new PDO("mysql:host=$host;dbname=$database_new;charset=utf8mb4", $user, $password);

    $pdo_old->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_new->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión establecida correctamente.\n";

    // Obtener clientes de CPARQUEbase
    $query = "SELECT id_clientes, codigo, barrio, domicilio, telefono, celular FROM Clientes";
    $stmt = $pdo_old->query($query);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$clientes) {
        echo "No se encontraron registros en la tabla Clientes de CPARQUEbase.\n";
        exit;
    }

    // Preparar la consulta de inserción en contactos
    $insert_query = "INSERT INTO contactos (cliente_id, barrio, domicilio, telefono, email, comentario, principal, created_at, updated_at)
                     VALUES (:cliente_id, :barrio, :domicilio, :telefono, :email, :comentario, :principal, NOW(), NOW())";

    $insert_stmt = $pdo_new->prepare($insert_query);
    $count = 0;

    foreach ($clientes as $cliente) {
        // Buscar el cliente en `cparque` basado en el `codigo`
        $codigo = !empty($cliente['codigo']) ? strval($cliente['codigo']) : '000000';
        $cliente_query = $pdo_new->prepare("SELECT id FROM clientes WHERE codigo = :codigo");
        $cliente_query->execute([':codigo' => $codigo]);
        $cliente_id = $cliente_query->fetchColumn();

        if (!$cliente_id) {
            echo "Advertencia: Cliente con Código {$codigo} no encontrado en cparque, omitiendo contactos.\n";
            continue;
        }

        // Asignar valores a los campos de contacto
        $barrio = !empty($cliente['barrio']) ? trim($cliente['barrio']) : null;
        $domicilio = !empty($cliente['domicilio']) ? trim($cliente['domicilio']) : null;
        $telefono = !empty($cliente['telefono']) ? trim($cliente['telefono']) : (!empty($cliente['celular']) ? trim($cliente['celular']) : null);
        $email = null;
        $comentario = null;
        $principal = 1;

        // Insertar el contacto
        $insert_stmt->execute([
            ':cliente_id' => $cliente_id,
            ':barrio' => $barrio,
            ':domicilio' => $domicilio,
            ':telefono' => $telefono,
            ':email' => $email,
            ':comentario' => $comentario,
            ':principal' => $principal
        ]);

        $count++;
        echo "Migrado contacto para Cliente ID: {$cliente_id} → Barrio: {$barrio}, Domicilio: {$domicilio}, Teléfono: {$telefono}\n";
    }

    echo "Migración completada. Total de registros migrados: $count\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
```

---

## Paso 3: Ejecutar el script de migración

Para ejecutar el script de migración, sigue estos pasos:

1. Guarda el código anterior en un archivo llamado `migrar_contactos.php`.
2. Asegúrate de que PHP esté instalado en tu sistema.
3. En la terminal, navega a la carpeta donde guardaste el archivo.
4. Ejecuta el siguiente comando:

    ```sh
    php migrar_contactos.php
    ```

---

## Paso 4: Mensajes esperados en la terminal

-   "Conexión establecida correctamente."
-   "Migrado contacto para Cliente ID: X → Barrio: ABC, Domicilio: Calle Falsa 123, Teléfono: 123456789"
-   "Advertencia: Cliente con Código YYY no encontrado en cparque, omitiendo contactos."
-   "Migración completada. Total de registros migrados: X."

Si hay errores, el script los mostrará en pantalla.
