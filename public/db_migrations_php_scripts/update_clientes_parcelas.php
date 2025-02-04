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
    $parcela_auxiliar_id = 8065;
    $parcela_auxiliar_query = 'SELECT id FROM parcelas WHERE id = :parcela_id';
    $stmt_auxiliar = $pdo->prepare($parcela_auxiliar_query);
    $stmt_auxiliar->execute([':parcela_id' => $parcela_auxiliar_id]);

    if (! $stmt_auxiliar->fetchColumn()) {
        echo "La parcela auxiliar no existe. Creándola...\n";
        $insert_auxiliar = "INSERT INTO parcelas (id, lote_id, numero, estado, descripcion, cliente_id, created_at, updated_at)
                            VALUES (:parcela_id, 120, 9999, 'libre', 'Parcela Auxiliar', 185000, NOW(), NOW())";
        $pdo->prepare($insert_auxiliar)->execute([':parcela_id' => $parcela_auxiliar_id]);
        echo "Parcela auxiliar creada con ID 8065.\n";
    }

    // Obtener los clientes de CPARQUEbase
    $query_clientes = "SELECT id_clientes, codigo, sector, lote, parcela FROM $database_cparque_base.Clientes";
    $stmt_clientes = $pdo->query($query_clientes);

    $update_query = "UPDATE parcelas
                     SET cliente_id = :cliente_id, estado = 'ocupada', updated_at = NOW()
                     WHERE id = :parcela_id";

    $stmt_update = $pdo->prepare($update_query);
    $count = 0;

    while ($cliente = $stmt_clientes->fetch(PDO::FETCH_ASSOC)) {
        $codigo_cliente = $cliente['codigo'];
        $sector = $cliente['sector'];
        $lote = $cliente['lote'];
        $parcela_numero = $cliente['parcela'];

        // Obtener el ID del cliente en cparque
        $query_cliente_id = 'SELECT id FROM clientes WHERE codigo = :codigo LIMIT 1';
        $stmt_cliente_id = $pdo->prepare($query_cliente_id);
        $stmt_cliente_id->execute([':codigo' => $codigo_cliente]);
        $cliente_id = $stmt_cliente_id->fetchColumn();

        if (! $cliente_id) {
            echo "Advertencia: Cliente con código {$codigo_cliente} no encontrado en cparque. Saltando...\n";

            continue;
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

        // Si no se encontró una parcela, asignar la auxiliar
        if (! $parcela_id) {
            echo "Parcela no encontrada para cliente {$codigo_cliente}. Asignando parcela auxiliar.\n";
            $parcela_id = $parcela_auxiliar_id;
        }

        // Actualizar la parcela con el cliente correspondiente
        $stmt_update->execute([
            ':cliente_id' => $cliente_id,
            ':parcela_id' => $parcela_id,
        ]);

        echo "Parcela ID {$parcela_id} asignada a Cliente ID {$cliente_id}.\n";
        $count++;
    }

    echo "Proceso completado. Total de parcelas migradas: $count\n";

} catch (PDOException $e) {
    exit('Error: '.$e->getMessage()."\n");
}
