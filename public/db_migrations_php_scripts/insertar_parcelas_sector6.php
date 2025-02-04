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

    $check_cliente_query = 'SELECT id FROM clientes WHERE id = :cliente_id';
    $check_stmt = $pdo->prepare($check_cliente_query);
    $check_stmt->execute([':cliente_id' => $cliente_id_predeterminado]);

    if (! $check_stmt->fetchColumn()) {
        echo "El cliente 185000 no existe. Creándolo...\n";
        $insert_cliente_query = "INSERT INTO clientes (id, dni, codigo, nombre, apellido, fecha_nacimiento, created_at, updated_at)
                                 VALUES (185000, '00000000', '185000', 'Parque Zenta S.R.L.', 'Predeterminado', '2000-01-01', NOW(), NOW())";
        $pdo->exec($insert_cliente_query);
        echo "Cliente 185000 creado con éxito.\n";
    }

    // Definir los lotes y su rango de parcelas en el Sector 6
    $lotes_parcelas = [
        1 => [1, 37], 2 => [38, 127], 3 => [128, 214], 4 => [215, 290], 5 => [291, 358],
        6 => [359, 418], 7 => [419, 468], 8 => [469, 512], 9 => [513, 546], 10 => [547, 574],
        11 => [575, 592], 12 => [593, 600],
    ];

    // Sector en el que se insertarán las parcelas
    $sector_id = 6;

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
        $lote_query = $pdo->prepare('SELECT id FROM lotes WHERE sector_id = :sector_id AND numero = :numero');
        $lote_query->execute([':sector_id' => $sector_id, ':numero' => $lote]);
        $lote_id = $lote_query->fetchColumn();

        if (! $lote_id) {
            echo "Advertencia: No se encontró lote {$lote} en sector {$sector_id}, omitiendo inserción de parcelas.\n";

            continue;
        }

        for ($parcela = $inicio; $parcela <= $fin; $parcela++) {
            $descripcion = "Parcela $parcela, Lote $lote, Sector $sector_id";

            $stmt->execute([
                ':lote_id' => $lote_id,
                ':numero' => $parcela,
                ':descripcion' => $descripcion,
                ':cliente_id' => $cliente_id_predeterminado,
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
    exit('Error: '.$e->getMessage()."\n");
}
