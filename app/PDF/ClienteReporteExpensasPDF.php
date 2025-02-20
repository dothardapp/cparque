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
        $this->Cell(95, 7, 'Nombre: ' . $this->data['nombre'], 0, 0, 'L', true);
        $this->Cell(95, 7, 'DNI: ' . $this->data['dni'], 0, 1, 'L', true);
        $this->Cell(190, 7, 'Domicilio: ' . $this->data['domicilio'], 0, 1, 'L', true);

        // ✅ Fecha del reporte en cursiva
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(190, 7, 'Fecha de Reporte: ' . $this->data['fecha'], 0, 1, 'L');

        $this->Ln(5);

        // ✅ Configurar encabezado de la tabla con colores
        $this->SetFillColor(50, 115, 220);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);

        // ✅ Encabezado de la tabla
        $this->Cell(55, 7, 'Parcela', 1, 0, 'C', true);
        $this->Cell(20, 7, 'Anio', 1, 0, 'C', true);
        $this->Cell(30, 7, 'Mes', 1, 0, 'C', true);
        $this->Cell(22, 7, 'Monto', 1, 0, 'C', true);
        $this->Cell(22, 7, 'Saldo', 1, 0, 'C', true);
        $this->Cell(35, 7, 'Estado', 1, 1, 'C', true);

        // Restaurar colores normales
        $this->SetTextColor(0, 0, 0);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }

    public function generarTabla()
    {
        $this->SetFont('Arial', '', 10);

        if (empty($this->data['expensas'])) {
            $this->Cell(0, 10, 'No hay expensas registradas.', 1, 1, 'C');
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
            $this->Cell(55, 7, $expensa->parcela->descripcion ?? 'Sin Parcela', 1, 0, 'C', $fill);
            $this->Cell(20, 7, $expensa->anio, 1, 0, 'C', $fill);
            $this->Cell(30, 7, $this->nombreMes($expensa->mes), 1, 0, 'C', $fill);
            $this->Cell(22, 7, '$' . number_format($expensa->monto, 2, ',', '.'), 1, 0, 'R', $fill);
            $this->Cell(22, 7, '$' . number_format($expensa->saldo, 2, ',', '.'), 1, 0, 'R', $fill);

            if ($expensa->estado === 'pendiente') {
                $this->SetFillColor(255, 77, 77);
                $this->SetTextColor(255, 255, 255);
            } elseif ($expensa->estado === 'pago parcial') {
                $this->SetFillColor(255, 192, 77);
                $this->SetTextColor(0, 0, 0);
            } else {
                $this->SetFillColor(92, 184, 92);
                $this->SetTextColor(255, 255, 255);
            }

            $this->Cell(35, 7, ucfirst($expensa->estado), 1, 1, 'C', true);
            $this->SetTextColor(0, 0, 0);
            $fill = !$fill;
        }
    }

    private function nombreMes($numeroMes)
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo',
            6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$numeroMes] ?? 'Desconocido';
    }
}
