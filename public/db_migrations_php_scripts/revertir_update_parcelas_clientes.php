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
    exit('Error: '.$e->getMessage()."\n");
}
