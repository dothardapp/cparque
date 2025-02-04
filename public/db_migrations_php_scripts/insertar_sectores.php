<?php

$host = 'localhost';
$user = 'root';
$password = 'joselote';
$database = 'cparque';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión establecida correctamente.\n";

    // Preparar la consulta de inserción con verificación previa
    $query = 'INSERT INTO sectors (numero, descripcion, created_at, updated_at)
              SELECT :numero, :descripcion, NOW(), NOW()
              WHERE NOT EXISTS (SELECT 1 FROM sectors WHERE numero = :numero)';

    $stmt = $pdo->prepare($query);
    $count = 0;

    for ($i = 1; $i <= 8; $i++) {
        $descripcion = "Sector $i";

        $stmt->execute([
            ':numero' => $i,
            ':descripcion' => $descripcion,
        ]);

        if ($stmt->rowCount() > 0) {
            echo "Sector insertado: Número {$i}, Descripción: {$descripcion}\n";
            $count++;
        } else {
            echo "Sector {$i} ya existe, no se insertó.\n";
        }
    }

    echo "Proceso completado. Total de sectores insertados: $count\n";

} catch (PDOException $e) {
    exit('Error: '.$e->getMessage()."\n");
}
