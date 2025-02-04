# Migración de la tabla `Clientes` de `CPARQUEbase` a `clientes` en `cparque`

Este documento describe el proceso de migración de la tabla `Clientes` de la base de datos `CPARQUEbase` a la tabla `clientes` en la base de datos `cparque`, incluyendo transformaciones de datos y la adición de un cliente especial.

---

## Paso 1: Transformaciones de datos

Se aplican las siguientes transformaciones para adaptar los datos de `CPARQUEbase` al nuevo esquema en `cparque`:

-   `id_clientes` → Ignorado (en `cparque` es autoincremental).
-   `dni (DOUBLE)` → Convertido a `VARCHAR(15)`. Si está vacío, se asigna `"00000000"`.
-   `codigo (DOUBLE)` → Convertido a `VARCHAR(50)`.
-   `nombre (CHAR(80))` → Convertido a `VARCHAR(100)`.
-   `apellido (CHAR(80))` → Convertido a `VARCHAR(100)`.
-   `fecha_nacimiento` → No existe en `CPARQUEbase`, se asigna `'1970-01-01'`.
-   `created_at` y `updated_at` → Se establecen con la fecha y hora actual.

---

## Paso 2: Cliente especial agregado

Al finalizar la migración de todos los clientes, se inserta el siguiente registro adicional:

```json
{
    "dni": "00000000",
    "codigo": "185000",
    "nombre": "Parque Zenta S.R.L.",
    "apellido": "Predeterminado",
    "fecha_nacimiento": "2000-01-01"
}
```

---

## Paso 3: Crear el script de migración

Guardar el siguiente código en un archivo llamado `migrar_clientes.php`:

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

    $query = "SELECT id_clientes, dni, codigo, nombre, Apellido FROM Clientes";
    $stmt = $pdo_old->query($query);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$clientes) {
        echo "No se encontraron registros en la tabla Clientes de CPARQUEbase.\n";
        exit;
    }

    $insert_query = "INSERT INTO clientes (dni, codigo, nombre, apellido, fecha_nacimiento, created_at, updated_at)
                     VALUES (:dni, :codigo, :nombre, :apellido, :fecha_nacimiento, NOW(), NOW())";

    $insert_stmt = $pdo_new->prepare($insert_query);
    $count = 0;

    foreach ($clientes as $cliente) {
        $dni = !empty($cliente['dni']) ? strval($cliente['dni']) : '00000000';
        $codigo = !empty($cliente['codigo']) ? strval($cliente['codigo']) : null;
        $nombre = trim($cliente['nombre']);
        $apellido = trim($cliente['Apellido']);
        $fecha_nacimiento = '1970-01-01';

        $insert_stmt->execute([
            ':dni' => $dni,
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':fecha_nacimiento' => $fecha_nacimiento
        ]);

        $count++;
        echo "Migrado cliente ID: {$cliente['id_clientes']} → DNI: {$dni}, Código: {$codigo}, Nombre: {$nombre} {$apellido}\n";
    }

    // Agregar el cliente especial "Parque Zenta S.R.L."
    $insert_stmt->execute([
        ':dni' => '00000000',
        ':codigo' => '185000',
        ':nombre' => 'Parque Zenta S.R.L.',
        ':apellido' => 'Predeterminado',
        ':fecha_nacimiento' => '2000-01-01'
    ]);

    echo "Cliente especial agregado: Parque Zenta S.R.L. (DNI: 00000000, Código: 185000)\n";

    echo "Migración completada. Total de registros migrados: $count + 1 (cliente especial).\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
```

---

## Paso 4: Ejecutar el script de migración

Para ejecutar el script de migración, sigue estos pasos:

1. Guarda el código anterior en un archivo llamado `migrar_clientes.php`.
2. Asegúrate de que PHP esté instalado en tu sistema.
3. En la terminal, navega a la carpeta donde guardaste el archivo.
4. Ejecuta el siguiente comando:

    ```sh
    php migrar_clientes.php
    ```

---

## Paso 5: Mensajes esperados en la terminal

-   "Conexión establecida correctamente."
-   "Migrado cliente ID: X → DNI: YYY, Código: ZZZ, Nombre: NNN AAA."
-   "Cliente especial agregado: Parque Zenta S.R.L. (DNI: 00000000, Código: 185000)"
-   "Migración completada. Total de registros migrados: X + 1 (cliente especial)."

Si hay errores, el script los mostrará en pantalla.
