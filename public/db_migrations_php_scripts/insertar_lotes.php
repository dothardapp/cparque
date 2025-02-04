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
        8 => 14,
    ];

    // Preparar la consulta de inserción con verificación previa
    $query = 'INSERT INTO lotes (sector_id, numero, descripcion, created_at, updated_at)
              SELECT :sector_id, :numero, :descripcion, NOW(), NOW()
              WHERE NOT EXISTS (
                  SELECT 1 FROM lotes WHERE sector_id = :sector_id AND numero = :numero
              )';

    $stmt = $pdo->prepare($query);
    $count = 0;

    foreach ($sectores_lotes as $sector => $cantidad_lotes) {
        // Obtener el sector_id desde la tabla sectors
        $sector_query = $pdo->prepare('SELECT id FROM sectors WHERE numero = :numero');
        $sector_query->execute([':numero' => $sector]);
        $sector_id = $sector_query->fetchColumn();

        if (! $sector_id) {
            echo "Advertencia: No se encontró sector con número {$sector}, omitiendo inserción de lotes.\n";

            continue;
        }

        for ($lote = 1; $lote <= $cantidad_lotes; $lote++) {
            $descripcion = "Lote $lote en sector $sector";

            $stmt->execute([
                ':sector_id' => $sector_id,
                ':numero' => $lote,
                ':descripcion' => $descripcion,
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
    exit('Error: '.$e->getMessage()."\n");
}
