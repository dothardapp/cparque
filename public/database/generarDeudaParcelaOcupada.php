#!/usr/bin/env php
<?php
/**
 * Genera expensas para cada PARCELA con estado = 'ocupada'.
 *
 * Para determinar la fecha "real" de ocupación,
 * busca la inhumación más antigua (MIN fecha_inhumacion)
 * asociada a esa parcela, excluyendo inhumados con cliente_id = 185000
 * (cliente "padre" o "fantasma").
 *
 * Solo se generan expensas en el año objetivo (--year).
 *   - Si la fecha de inhumación más antigua es posterior al año objetivo => no genera nada.
 *   - Si la fecha de inhumación más antigua es anterior al año objetivo => genera de enero a diciembre (o hasta mes actual si el año objetivo == año en curso).
 *   - Si la fecha de inhumación más antigua es el mismo año objetivo => genera desde ese mes de inhumación hasta diciembre (o hasta mes actual si corresponde).
 *
 * Se ignora cualquier parcela cuyo cliente_id sea 185000 (parcela libre).
 * Se omiten también las parcelas que no tienen inhumados "reales".
 *
 * USO:
 *   php generarDeudaParcelaOcupada.php --year=2025 --monto=5000 --user=1
 */

// -----------------------------------------------------
// 1. Parámetros de conexión
// -----------------------------------------------------
$host     = 'localhost';
$dbUser   = 'root';
$dbPass   = 'joselote';
$database = 'cparque';

// -----------------------------------------------------
// 2. Lectura de parámetros desde consola
// -----------------------------------------------------
$yearObjetivo = null;
$monto        = 5000; // Valor por defecto
$user_id      = 1;    // Valor por defecto

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
    echo "Uso: php generarDeudaParcelaOcupada.php --year=AAAA [--monto=XXXX] [--user=YY]\n";
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
// 4. Zona horaria, año/mes actual
// -----------------------------------------------------
date_default_timezone_set('America/Argentina/Buenos_Aires');
$hoy        = new DateTime();
$anioActual = (int)$hoy->format('Y');
$mesActual  = (int)$hoy->format('m');

// -----------------------------------------------------
// 5. OBTENER PARCELAS OCUPADAS
// -----------------------------------------------------
$sqlParcelasOcupadas = "
    SELECT p.id AS parcela_id,
           p.cliente_id
      FROM parcelas p
     WHERE p.estado = 'ocupada'
       AND p.cliente_id != 185000
";
$stmtParcelas = $pdo->query($sqlParcelasOcupadas);
$parcelas = $stmtParcelas->fetchAll(PDO::FETCH_ASSOC);

if (!$parcelas) {
    echo "No hay parcelas con estado 'ocupada' (o todas están con cliente_id=185000).\n";
    exit(0);
}

// -----------------------------------------------------
// 6. OBTENER FECHA DE INHUMACIÓN MÁS ANTIGUA POR PARCELA
//    (excluyendo cliente_id=185000 en inhumados)
// -----------------------------------------------------
$sqlInhumacionAntigua = "
    SELECT parcela_id,
           MIN(fecha_inhumacion) AS fecha_inhumacion_mas_antigua
      FROM inhumados
     WHERE cliente_id != 185000
     GROUP BY parcela_id
";
// Esto nos devuelve un map: parcela_id -> fecha_inhumacion_mas_antigua (si existe)
$stmtInhum = $pdo->query($sqlInhumacionAntigua);
$inhumaciones = $stmtInhum->fetchAll(PDO::FETCH_ASSOC);

// Convertimos a un array asociativo para fácil acceso:
// $mapaInhumacion[parcela_id] = 'YYYY-MM-DD'
$mapaInhumacion = [];
foreach ($inhumaciones as $row) {
    $mapaInhumacion[$row['parcela_id']] = $row['fecha_inhumacion_mas_antigua'];
}

// -----------------------------------------------------
// 7. Generar expensas por cada parcela ocupada
// -----------------------------------------------------
echo "Generando expensas para el año $yearObjetivo...\n\n";

$clientesAfectados = [];  // IDs de clientes a los que se les creó expensas

foreach ($parcelas as $idx => $par) {
    $parcelaId = $par['parcela_id'];
    $clienteId = $par['cliente_id'];

    echo "--------------------------------------------\n";
    echo "Parcela #" . ($idx + 1) . ": ID $parcelaId\n";
    echo "Cliente asignado: $clienteId\n";

    // 7.1 Ver si existe fecha_inhumacion_mas_antigua en $mapaInhumacion
    if (!isset($mapaInhumacion[$parcelaId])) {
        // Significa que esta parcela, aunque está 'ocupada',
        // no aparece en la tabla inhumados con un cliente_id != 185000.
        // Podrías considerarlo inconsistencia o algo, aquí omitimos.
        echo "  > No se encontró inhumación real (cliente != 185000). Omitiendo...\n";
        continue;
    }

    $fechaInhStr = $mapaInhumacion[$parcelaId];
    $fechaInh    = new DateTime($fechaInhStr);
    $anioInh     = (int)$fechaInh->format('Y');
    $mesInh      = (int)$fechaInh->format('m');

    echo "  > Fecha de inhumación más antigua: " . $fechaInh->format('Y-m-d') . "\n";

    // 7.2 Determinar si la fecha de inhumación es posterior al año objetivo
    if ($anioInh > $yearObjetivo) {
        echo "  > El año de inhumación ($anioInh) es mayor que el año objetivo ($yearObjetivo). Omitiendo...\n";
        continue;
    }

    // 7.3 Definir mesInicial y mesFinal para generar en el año objetivo
    //     - Si anioInh < yearObjetivo => generamos desde enero (1).
    //     - Si anioInh == yearObjetivo => generamos desde $mesInh.
    $mesInicio = 1;
    if ($anioInh == $yearObjetivo) {
        $mesInicio = $mesInh;
    }

    $mesFin = 12;
    if ($yearObjetivo == $anioActual) {
        // Para no crear expensas en meses futuros al actual
        $mesFin = min($mesFin, $mesActual);
    }

    if ($mesInicio > $mesFin) {
        echo "  > Rango de meses inválido: mesInicio=$mesInicio > mesFin=$mesFin. Omitiendo...\n";
        continue;
    }

    $seCreoAlMenosUna = false;

    // 7.4 Generar expensas del mesInicio al mesFin
    for ($mes = $mesInicio; $mes <= $mesFin; $mes++) {
        // Verificar si ya existe la expensa
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
            // Crear la expensa
            $sqlInsert = "
                INSERT INTO expensas
                    (parcela_id, cliente_id, anio, mes,
                     monto, saldo, estado, user_id,
                     created_at, updated_at)
                VALUES
                    (:parcela_id, :cliente_id, :anio, :mes,
                     :monto, 0, 'pendiente', :user_id,
                     NOW(), NOW())
            ";
            $stmtIns = $pdo->prepare($sqlInsert);
            $stmtIns->execute([
                ':parcela_id' => $parcelaId,
                ':cliente_id' => $clienteId, // Tomamos el de la parcela
                ':anio'       => $yearObjetivo,
                ':mes'        => $mes,
                ':monto'      => $monto,
                ':user_id'    => $user_id,
            ]);

            echo "    - Expensa creada: año $yearObjetivo, mes $mes, monto=$monto\n";
            $seCreoAlMenosUna = true;
        } else {
            // echo "    - Expensa ya existe (año=$yearObjetivo, mes=$mes). Omitiendo.\n";
        }
    }

    if ($seCreoAlMenosUna) {
        $clientesAfectados[] = $clienteId;
        echo "  => Se generaron expensas para el cliente $clienteId.\n";
    } else {
        echo "  => No se generaron expensas nuevas.\n";
    }

    echo "--------------------------------------------\n\n";
}

// -----------------------------------------------------
// 8. Resumen final
// -----------------------------------------------------
$clientesAfectados = array_unique($clientesAfectados);
$totalAfectados = count($clientesAfectados);

echo "Proceso de generación de expensas para el año $yearObjetivo finalizado.\n";
echo "Total de clientes afectados: $totalAfectados.\n\n";

if ($totalAfectados > 0) {
    // Convertimos cada id a int (por seguridad) y armamos la lista
    $listaIds = implode(',', array_map('intval', $clientesAfectados));

    // Consulta masiva
    $sqlMasiva = "SELECT id, dni, nombre, apellido, codigo
                    FROM clientes
                   WHERE id IN ($listaIds)";
    $stmtMasiva = $pdo->query($sqlMasiva);
    $resultados = $stmtMasiva->fetchAll(PDO::FETCH_ASSOC);

    // Convertir a un array asociativo [ id => [dni, nombre, ...] ]
    $detallesClientes = [];
    foreach ($resultados as $row) {
        $detallesClientes[$row['id']] = $row;
    }

    echo "Detalle de clientes afectados:\n\n";

    // Recorremos en el orden de $clientesAfectados
    foreach ($clientesAfectados as $idCliente) {
        if (isset($detallesClientes[$idCliente])) {
            $c = $detallesClientes[$idCliente];
            echo " - [ID: $idCliente] ";
            echo "DNI: {$c['dni']} | ";
            echo "Nombre: {$c['nombre']} | ";
            echo "Apellido: {$c['apellido']} | ";
            echo "Código: {$c['codigo']}\n";
        } else {
            echo " - [ID: $idCliente] Cliente no encontrado.\n";
        }
    }
}

echo "\nFin del proceso.\n";
?>
