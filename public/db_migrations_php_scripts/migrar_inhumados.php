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
    $parcela_auxiliar_id = 8063;
    $parcela_auxiliar_query = 'SELECT id FROM parcelas WHERE id = :parcela_id';
    $stmt_auxiliar = $pdo->prepare($parcela_auxiliar_query);
    $stmt_auxiliar->execute([':parcela_id' => $parcela_auxiliar_id]);

    if (! $stmt_auxiliar->fetchColumn()) {
        echo "La parcela auxiliar no existe. Creándola...\n";
        $insert_auxiliar = "INSERT INTO parcelas (id, lote_id, numero, estado, descripcion, cliente_id, created_at, updated_at)
                            VALUES (:parcela_id, 120, 9998, 'libre', 'Parcela Auxiliar', 185000, NOW(), NOW())";
        $pdo->prepare($insert_auxiliar)->execute([':parcela_id' => $parcela_auxiliar_id]);
        echo "Parcela auxiliar creada con ID 8063.\n";
    }

    // Obtener los inhumados de CPARQUEbase
    $query_inhumados = "SELECT nombre, DATE(fecha) AS fecha_inhumacion, cliente, sector, lote, parcela
                         FROM $database_cparque_base.Inhumaciones
                         ORDER BY fecha ASC";
    $stmt_inhumados = $pdo->query($query_inhumados);

    $insert_query = "INSERT INTO inhumados (cliente_id, parcela_id, nivel, nombre, apellido, fecha_nacimiento, fecha_inhumacion, created_at, updated_at)
                     VALUES (:cliente_id, :parcela_id, :nivel, :nombre, '', '1970-01-01', :fecha_inhumacion, NOW(), NOW())";

    $stmt_insert = $pdo->prepare($insert_query);
    $count = 0;

    while ($inhumado = $stmt_inhumados->fetch(PDO::FETCH_ASSOC)) {
        $nombre = $inhumado['nombre'];
        $fecha_inhumacion = $inhumado['fecha_inhumacion'];
        $codigo_cliente = $inhumado['cliente'];
        $sector = $inhumado['sector'];
        $lote = $inhumado['lote'];
        $parcela_numero = $inhumado['parcela'];

        // Obtener el ID del cliente en cparque
        $query_cliente_id = 'SELECT id FROM clientes WHERE codigo = :codigo LIMIT 1';
        $stmt_cliente_id = $pdo->prepare($query_cliente_id);
        $stmt_cliente_id->execute([':codigo' => $codigo_cliente]);
        $cliente_id = $stmt_cliente_id->fetchColumn();

        if (! $cliente_id) {
            echo "Advertencia: Cliente con código {$codigo_cliente} no encontrado en cparque. Asignando ID 185000.\n";
            $cliente_id = 185000; // Asignar a Parque Zenta S.R.L.
        }

        // Buscar la parcela correspondiente
        $query_parcela = 'SELECT p.id FROM parcelas p
                          JOIN lotes l ON p.lote_id = l.id
                          JOIN sectors s ON l.sector_id = s.id
                          WHERE s.numero = :sector AND l.numero = :lote AND p.numero = :parcela_numero
                          LIMIT 1';
        $stmt_parcela = $pdo->prepare($query_parcela);
        $stmt_parcela->execute([':sector' => $sector, ':lote' => $lote, ':parcela_numero' => $parcela_numero]);
        $parcela_id = $stmt_parcela->fetchColumn();

        // Si la parcela no existe, asignar la auxiliar
        if (! $parcela_id) {
            echo "Parcela no encontrada para cliente {$codigo_cliente}. Asignando parcela auxiliar.\n";
            $parcela_id = $parcela_auxiliar_id;
        }

        // Determinar el nivel en la parcela
        $query_nivel = 'SELECT COUNT(*) AS cantidad FROM inhumados WHERE parcela_id = :parcela_id';
        $stmt_nivel = $pdo->prepare($query_nivel);
        $stmt_nivel->execute([':parcela_id' => $parcela_id]);
        $cantidad = $stmt_nivel->fetchColumn();

        $niveles = ['primer_nivel', 'segundo_nivel', 'tercer_nivel'];
        $nivel = $niveles[min($cantidad, 2)];

        // Insertar el inhumado
        $stmt_insert->execute([
            ':cliente_id' => $cliente_id,
            ':parcela_id' => $parcela_id,
            ':nivel' => $nivel,
            ':nombre' => $nombre,
            ':fecha_inhumacion' => $fecha_inhumacion,
        ]);

        echo "Inhumado '{$nombre}' agregado a Parcela ID {$parcela_id} (Nivel: {$nivel}).\n";
        $count++;
    }

    echo "Proceso completado. Total de inhumados migrados: $count\n";

} catch (PDOException $e) {
    exit('Error: '.$e->getMessage()."\n");
}
