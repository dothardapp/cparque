<?php

$host = 'localhost';
$dbname_old = 'CPARQUEbase';
$dbname_new = 'cparque';
$username = 'root';
$password = 'joselote'; // Cambia esto por la contraseña correcta

$log_file = '/home/joselo/cparque/storage/logs/migrar_expensas.log';
$clientes_sin_parcela = [];

function writeLog($message) {
    global $log_file;
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

try {
    // Conexión a ambas bases de datos
    $pdo_old = new PDO("mysql:host=$host;dbname=$dbname_old;charset=utf8mb4", $username, $password);
    $pdo_new = new PDO("mysql:host=$host;dbname=$dbname_new;charset=utf8mb4", $username, $password);

    $pdo_old->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_new->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión establecida correctamente.\n";
    writeLog("Conexión establecida correctamente.");

    // Obtener registros de la tabla antigua
    $stmt_old = $pdo_old->query('SELECT id_ctacte, codigo, ano, mes, debe FROM CtaCte WHERE debe > 0');
    $registros = $stmt_old->fetchAll(PDO::FETCH_ASSOC);

    echo 'Total de registros a migrar: '.count($registros).PHP_EOL;

    // Preparar la consulta de inserción en la nueva base de datos
    $insert_stmt = $pdo_new->prepare('INSERT INTO expensas (parcela_id, cliente_id, anio, mes, monto, saldo, estado, user_id)
        VALUES (:parcela_id, :cliente_id, :anio, :mes, :monto, :saldo, :estado, :user_id)
        ON DUPLICATE KEY UPDATE saldo = saldo + VALUES(saldo)');

    $error_count = 0;
    foreach ($registros as $index => $row) {
        try {
            // Obtener parcela_id y cliente_id
            $stmt_parcelas = $pdo_new->prepare('SELECT id, cliente_id FROM parcelas WHERE cliente_id = (SELECT id FROM clientes WHERE codigo = :codigo LIMIT 1)');
            $stmt_parcelas->execute([':codigo' => $row['codigo']]);
            $parcelas = $stmt_parcelas->fetchAll(PDO::FETCH_ASSOC);

            if (empty($parcelas)) {
                if (!isset($clientes_sin_parcela[$row['codigo']])) {
                    $clientes_sin_parcela[$row['codigo']] = true;
                    $error_message = "❌ Error: No se encontró una parcela para el cliente con código {$row['codigo']}.";
                    echo $error_message . "\n";
                    writeLog($error_message);
                }
                continue;
            }

            foreach ($parcelas as $parcela) {
                $parcela_id = $parcela['id'];
                $cliente_id = $parcela['cliente_id'];

                // Preparar datos para inserción con valores por defecto
                $params = [
                    'parcela_id' => (int) $parcela_id,
                    'cliente_id' => (int) $cliente_id,
                    'anio' => (int) $row['ano'],
                    'mes' => (int) $row['mes'],
                    'monto' => (float) $row['debe'],
                    'saldo' => (float) $row['debe'],
                    'estado' => 'pendiente',
                    'user_id' => 1 // Usuario predeterminado
                ];

                // Ejecutar inserción
                $insert_stmt->execute($params);

                echo "[$index] Insertado: Parcela ID {$parcela_id}, Cliente ID {$cliente_id}, Año {$params['anio']}, Mes {$params['mes']}, Monto {$params['monto']}, Saldo {$params['saldo']}".PHP_EOL;
            }
        } catch (Exception $e) {
            $error_count++;
            $error_message = "❌ Error en la inserción del registro $index: " . $e->getMessage();
            echo $error_message . PHP_EOL;
            writeLog($error_message);
        }
    }

    $clientes_sin_parcela_count = count($clientes_sin_parcela);
    $completion_message = "✅ Migración completada con $error_count errores. No se encontraron parcelas para $clientes_sin_parcela_count clientes.";
    echo $completion_message . PHP_EOL;
    writeLog($completion_message);

} catch (PDOException $e) {
    $error_message = '❌ Error de conexión: ' . $e->getMessage();
    echo $error_message . PHP_EOL;
    writeLog($error_message);
    exit($error_message);
}
