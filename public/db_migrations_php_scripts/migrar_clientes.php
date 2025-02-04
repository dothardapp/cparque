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

    $query = 'SELECT id_clientes, dni, codigo, nombre, Apellido FROM Clientes';
    $stmt = $pdo_old->query($query);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (! $clientes) {
        echo "No se encontraron registros en la tabla Clientes de CPARQUEbase.\n";
        exit;
    }

    $insert_query = 'INSERT INTO clientes (dni, codigo, nombre, apellido, fecha_nacimiento, created_at, updated_at)
                     VALUES (:dni, :codigo, :nombre, :apellido, :fecha_nacimiento, NOW(), NOW())';

    $insert_stmt = $pdo_new->prepare($insert_query);
    $count = 0;

    foreach ($clientes as $cliente) {
        $dni = ! empty($cliente['dni']) ? strval($cliente['dni']) : '00000000';
        $codigo = ! empty($cliente['codigo']) ? strval($cliente['codigo']) : null;
        $nombre = trim($cliente['nombre']);
        $apellido = trim($cliente['Apellido']);
        $fecha_nacimiento = '1970-01-01';

        $insert_stmt->execute([
            ':dni' => $dni,
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':fecha_nacimiento' => $fecha_nacimiento,
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
        ':fecha_nacimiento' => '2000-01-01',
    ]);

    echo "Cliente especial agregado: Parque Zenta S.R.L. (DNI: 00000000, Código: 185000)\n";

    echo "Migración completada. Total de registros migrados: $count + 1 (cliente especial).\n";

} catch (PDOException $e) {
    exit('Error: '.$e->getMessage()."\n");
}
