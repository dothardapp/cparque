#!/usr/bin/env php
<?php
/**
 * Script para generar expensas solo del año objetivo,
 * respetando la fecha de ingreso (fecha_nacimiento) de cada cliente
 * y OMITIENDO al cliente con id = 185000.
 */

// -----------------------------------------------------
// 1. Parámetros de conexión
// -----------------------------------------------------
$host     = 'localhost';
$dbUser   = 'root';
$dbPass   = 'joselote';
$database = 'cparque';

// -----------------------------------------------------
// 2. Lectura de parámetros
// -----------------------------------------------------
$yearObjetivo = null;
$monto        = 5000; // valor por defecto
$user_id      = 1;    // valor por defecto

foreach ($argv as $arg) {
    if (strpos($arg, '--year=') === 0) {
        $yearObjetivo = (int)substr($arg, strlen('--year='));
    } elseif (strpos($arg, '--monto=') === 0) {
        $monto = (float)substr($arg, strlen('--monto='));
    } elseif (strpos($arg, '--user=') === 0) {
        $user_id = (int)substr($arg, strlen('--user='));
    }
}

if (!$yearObjetivo) {
    echo "Uso: php generarDeudaSoloAnio.php --year=AAAA [--monto=XXXX] [--user=YY]\n";
    exit(1);
}

// -----------------------------------------------------
// 3. Conexión PDO
// -----------------------------------------------------
try {
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}

// -----------------------------------------------------
// 4. Configuración de zona horaria
// -----------------------------------------------------
date_default_timezone_set('America/Argentina/Buenos_Aires');
$fechaHoy    = new DateTime();
$anioActual  = (int)$fechaHoy->format('Y');
$mesActual   = (int)$fechaHoy->format('m');

// -----------------------------------------------------
// 5. Obtener lista de clientes
// -----------------------------------------------------
$sqlClientes = "SELECT id, nombre, apellido, fecha_nacimiento AS fecha_ingreso
                FROM clientes";
$stmtClientes = $pdo->query($sqlClientes);
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

if (!$clientes) {
    echo "No se encontraron clientes.\n";
    exit(0);
}

echo "Iniciando generación de expensas para el año $yearObjetivo.\n";
echo "(Respetando la fecha de ingreso y omitiendo al cliente con id = 185000)\n\n";

$clientesAfectados = [];

foreach ($clientes as $index => $cliente) {
    $clienteId   = $cliente['id'];
    $nombre      = $cliente['nombre'];
    $apellido    = $cliente['apellido'];
    $fechaIngreso= new DateTime($cliente['fecha_ingreso']); // 'fecha_nacimiento' usada como ingreso
    $anioIngreso = (int)$fechaIngreso->format('Y');
    $mesIngreso  = (int)$fechaIngreso->format('m');

    // -----------------------------------------------------
    //  OMITE AL CLIENTE CON ID = 185000
    // -----------------------------------------------------
    if ($clienteId == 185000) {
        echo "-------------------------------------------\n";
        echo "Cliente #".($index+1)." (ID: $clienteId) - $apellido, $nombre\n";
        echo "  > Cliente omitido (ID 185000).\n\n";
        continue;
    }

    // Mensaje de progreso
    echo "-------------------------------------------\n";
    echo "Cliente #".($index+1)." (ID: $clienteId) - $apellido, $nombre\n";
    echo "Ingreso: " . $fechaIngreso->format('Y-m-d') . "\n";

    // 5.1 Verificar si el año de ingreso es mayor que el objetivo
    if ($anioIngreso > $yearObjetivo) {
        echo "  > El año de ingreso ($anioIngreso) es posterior al año objetivo ($yearObjetivo). Omitiendo...\n";
        continue;
    }

    // 5.2 Obtener las parcelas del cliente
    $sqlParcelas = "SELECT id FROM parcelas WHERE cliente_id = :cliente_id";
    $stmtParcelas = $pdo->prepare($sqlParcelas);
    $stmtParcelas->execute([':cliente_id' => $clienteId]);
    $parcelasCliente = $stmtParcelas->fetchAll(PDO::FETCH_ASSOC);

    if (!$parcelasCliente) {
        echo "  > El cliente no tiene parcelas asociadas.\n";
        continue;
    }

    // 5.3 Definir rango de meses a generar (en el año objetivo)
    $mesInicio = 1;
    if ($anioIngreso == $yearObjetivo) {
        // Si coincide el año de ingreso, arrancar desde el mes de ingreso
        $mesInicio = $mesIngreso;
    }

    $mesFin = 12;
    // Si es el año actual, opcionalmente generamos solo hasta el mes actual
    if ($yearObjetivo == $anioActual) {
        $mesFin = min($mesFin, $mesActual);
    }

    if ($mesInicio > $mesFin) {
        echo "  > No se generan expensas: mesInicio ($mesInicio) > mesFin ($mesFin).\n";
        continue;
    }

    $seCreoAlMenosUna = false;

    // 5.4 Generar las expensas mes a mes
    foreach ($parcelasCliente as $parcela) {
        $parcelaId = $parcela['id'];
        echo "  > Parcela ID: $parcelaId\n";

        for ($mes = $mesInicio; $mes <= $mesFin; $mes++) {
            // Verificar si existe la expensa (parcela_id, anio=yearObjetivo, mes)
            $sqlExiste = "
                SELECT COUNT(*) as cnt
                FROM expensas
                WHERE parcela_id = :parcela_id
                  AND anio       = :anio
                  AND mes        = :mes
            ";
            $stmtExiste = $pdo->prepare($sqlExiste);
            $stmtExiste->execute([
                ':parcela_id' => $parcelaId,
                ':anio'       => $yearObjetivo,
                ':mes'        => $mes
            ]);
            $rowExiste = $stmtExiste->fetch(PDO::FETCH_ASSOC);

            if ($rowExiste['cnt'] == 0) {
                // Insertar expensa
                $sqlInsert = "
                    INSERT INTO expensas
                        (parcela_id, cliente_id, anio, mes,
                         monto, saldo, estado, user_id,
                         created_at, updated_at)
                    VALUES
                        (:parcela_id, :cliente_id, :anio, :mes,
                         :monto, :saldo, :estado, :user_id,
                         NOW(), NOW())
                ";
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    ':parcela_id' => $parcelaId,
                    ':cliente_id' => $clienteId,
                    ':anio'       => $yearObjetivo,
                    ':mes'        => $mes,
                    ':monto'      => $monto,
                    ':saldo'      => 0,
                    ':estado'     => 'pendiente',
                    ':user_id'    => $user_id,
                ]);

                echo "    - Creada expensa: AÑO $yearObjetivo, MES $mes, Monto $monto\n";
                $seCreoAlMenosUna = true;
            } else {
                // echo "    - Expensa ya existe (AÑO $yearObjetivo, MES $mes). Omitiendo...\n";
            }
        }
    }

    if ($seCreoAlMenosUna) {
        $clientesAfectados[] = $clienteId;
        echo "  => Se generaron expensas nuevas para este cliente.\n";
    } else {
        echo "  => No se crearon expensas nuevas.\n";
    }

    echo "-------------------------------------------\n\n";
}

// -----------------------------------------------------
// 6. Mostrar resultados
// -----------------------------------------------------
$totalAfectados = count($clientesAfectados);
echo "Finalizado el proceso de generación de expensas para el año $yearObjetivo.\n";
echo "Total de clientes con expensas nuevas: $totalAfectados.\n\n";

if ($totalAfectados > 0) {
    echo "IDs de los clientes afectados:\n";
    foreach ($clientesAfectados as $idCliente) {
        echo " - Cliente ID: $idCliente\n";
    }
}

echo "\nProceso finalizado.\n";
?>
