<?php

namespace App\PDF;

use FPDF;

class ClienteReporteExpensasPDF extends FPDF
{
    protected $data;

    public function __construct($data)
    {
        parent::__construct();
        $this->data = $data;
        $this->AliasNbPages(); // ✅ Soluciona la paginación en el footer
    }

    private function convertirTexto($texto)
    {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }

    public function Header()
    {
        // ✅ Agregar Logo (ajustado a la derecha con menor tamaño)
        $this->Image(public_path('LogoParque_01_433x340.png'), 160, 5, 25);

        // ✅ Nombre de la empresa
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(10, 10);
        $this->Cell(190, 10, 'Parque Zenta S.R.L.', 0, 1, 'C');

        // ✅ Subtítulo "Reporte de Expensas del Cliente"
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY(10, 18);
        $this->Cell(190, 10, 'Reporte de Expensas del Cliente', 0, 1, 'C');

        // ✅ Línea divisoria
        $this->SetLineWidth(0.5);
        $this->Line(10, 30, 200, 30);

        // ✅ Datos del Cliente con fondo gris claro
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(240, 240, 240); // Gris claro
        $this->SetXY(10, 33);
        $this->Cell(95, 7, $this->convertirTexto('Nombre: ' . $this->data['nombre']), 0, 0, 'L', true);
        $this->Cell(95, 7, $this->convertirTexto('DNI: ' . $this->data['dni']), 0, 1, 'L', true);

        // ✅ Cálculo de deuda total
        $totalDeuda = collect($this->data['expensas'])->sum('saldo');

        // ✅ Continuación con el domicilio
        $this->SetFillColor(240, 240, 240); // Restaurar fondo gris claro
        $this->Cell(190, 7, $this->convertirTexto('Domicilio: ' . $this->data['domicilio']), 0, 1, 'L', true);

        // ✅ Fondo de color según deuda
        if ($totalDeuda > 0) {
            $this->SetFillColor(255, 221, 221); // 🔴 Rojo claro si hay deuda
        } else {
            $this->SetFillColor(221, 255, 221); // 🟢 Verde claro si NO hay deuda
        }

        $this->Cell(190, 7, $this->convertirTexto('PENDIENTE DE PAGO: $' . number_format($totalDeuda, 2, ',', '.')), 0, 1, 'R', true);

        // ✅ Fecha del reporte en cursiva
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(190, 7, $this->convertirTexto('Fecha de Reporte: ' . $this->data['fecha']), 0, 1, 'L');

        $this->Ln(5);
    }


    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, $this->convertirTexto('Página ' . $this->PageNo() . ' de ') . '{nb}', 0, 0, 'C');
    }

    public function generarTabla()
    {
        $this->SetFont('Arial', '', 10);

        if (empty($this->data['expensas'])) {
            $this->Cell(0, 10, $this->convertirTexto('No hay expensas registradas.'), 1, 1, 'C');
            return;
        }

        $expensasOrdenadas = collect($this->data['expensas'])->sortBy([
            fn($a, $b) => strcmp($a->parcela->descripcion ?? '', $b->parcela->descripcion ?? ''),
            fn($a, $b) => $a->anio <=> $b->anio,
            fn($a, $b) => $a->mes <=> $b->mes,
        ]);

        $fill = false;
        foreach ($expensasOrdenadas as $expensa) {
            $this->SetFillColor(230, 240, 255);
            $this->Cell(55, 7, $this->convertirTexto($expensa->parcela->descripcion ?? 'Sin Parcela'), 1, 0, 'C', $fill);
            $this->Cell(20, 7, $this->convertirTexto($expensa->anio), 1, 0, 'C', $fill);
            $this->Cell(30, 7, $this->convertirTexto($this->nombreMes($expensa->mes)), 1, 0, 'C', $fill);
            $this->Cell(22, 7, $this->convertirTexto('$' . number_format($expensa->monto, 2, ',', '.')), 1, 0, 'R', $fill);
            $this->Cell(22, 7, $this->convertirTexto('$' . number_format($expensa->saldo, 2, ',', '.')), 1, 0, 'R', $fill);

            if ($expensa->estado === 'pendiente') {
                $this->SetFillColor(255, 77, 77);
                $this->SetTextColor(255, 255, 255);
            } elseif ($expensa->estado === 'pagado parcialmente') {
                $this->SetFillColor(255, 192, 77);
                $this->SetTextColor(0, 0, 0);
                $expensa->estado = 'Pago parcial'; // ✅ Cambio aquí el texto antes de imprimir
            } else {
                $this->SetFillColor(92, 184, 92);
                $this->SetTextColor(255, 255, 255);
            }

            $this->Cell(35, 7, $this->convertirTexto(ucfirst($expensa->estado)), 1, 1, 'C', true);
            $this->SetTextColor(0, 0, 0);
            $fill = !$fill;
        }
    }

    private function nombreMes($numeroMes)
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        return $this->convertirTexto($meses[$numeroMes] ?? 'Desconocido');
    }
}
