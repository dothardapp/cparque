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
        12 => 16, 13 => 16, 14 => 16, 15 => 12, 16 => 8,
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
        $lote_query = $pdo->prepare('SELECT id FROM lotes WHERE sector_id = :sector_id AND numero = :numero');
        $lote_query->execute([':sector_id' => $sector_id, ':numero' => $lote]);
        $lote_id = $lote_query->fetchColumn();

        if (! $lote_id) {
            echo "Advertencia: No se encontró lote {$lote} en sector {$sector_id}, omitiendo inserción de parcelas.\n";

            continue;
        }

        for ($parcela = 1; $parcela <= $cantidad_parcelas; $parcela++) {
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
