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
    $query = 'SELECT id_clientes, codigo, barrio, domicilio, telefono, celular FROM Clientes';
    $stmt = $pdo_old->query($query);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (! $clientes) {
        echo "No se encontraron registros en la tabla Clientes de CPARQUEbase.\n";
        exit;
    }

    // Preparar la consulta de inserción en contactos
    $insert_query = 'INSERT INTO contactos (cliente_id, barrio, domicilio, telefono, email, comentario, principal, created_at, updated_at)
                     VALUES (:cliente_id, :barrio, :domicilio, :telefono, :email, :comentario, :principal, NOW(), NOW())';

    $insert_stmt = $pdo_new->prepare($insert_query);
    $count = 0;

    foreach ($clientes as $cliente) {
        // Buscar el cliente en `cparque` basado en el `codigo`
        $codigo = ! empty($cliente['codigo']) ? strval($cliente['codigo']) : '000000';
        $cliente_query = $pdo_new->prepare('SELECT id FROM clientes WHERE codigo = :codigo');
        $cliente_query->execute([':codigo' => $codigo]);
        $cliente_id = $cliente_query->fetchColumn();

        if (! $cliente_id) {
            echo "Advertencia: Cliente con Código {$codigo} no encontrado en cparque, omitiendo contactos.\n";

            continue;
        }

        // Asignar valores a los campos de contacto
        $barrio = ! empty($cliente['barrio']) ? trim($cliente['barrio']) : null;
        $domicilio = ! empty($cliente['domicilio']) ? trim($cliente['domicilio']) : 'Sin domicilio - ACTUALIZAR';
        $telefono = ! empty($cliente['telefono']) ? trim($cliente['telefono']) : (! empty($cliente['celular']) ? trim($cliente['celular']) : null);
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
            ':principal' => $principal,
        ]);

        $count++;
        echo "Migrado contacto para Cliente ID: {$cliente_id} → Barrio: {$barrio}, Domicilio: {$domicilio}, Teléfono: {$telefono}\n";
    }

    echo "Migración completada. Total de registros migrados: $count\n";

} catch (PDOException $e) {
    exit('Error: '.$e->getMessage()."\n");
}
