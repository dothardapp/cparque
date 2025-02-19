<?php

namespace App\PDF;

use FPDF;

class ReciboPDF extends FPDF
{
    protected $data;

    public function __construct($data)
    {
        parent::__construct();
        $this->data = $data;
    }

    public function Header()
    {
        // Agregar la imagen de fondo (recibo_base.jpg debe estar en public/)
        $this->Image(
            base_path('public/recibo_parque_02.jpg'), // Ruta de la imagen
            5,     // Posición X (Izquierda de la hoja)
            5,     // Posición Y (Arriba de la hoja)
            200,   // Ancho en mm (Máximo en A4)
            135,   // Alto en mm (Mitad de una hoja A4)
            'JPG'  // Formato de la imagen (opcional, mejora compatibilidad)
        );

        // Configurar fuente
        $this->SetFont('Arial', '', 11);

        // Número de recibo
        $this->SetXY(110, 5);
        $this->Cell(30, 10, $this->data['numero_recibo'], 0, 1, 'C');

        // Fecha
        $this->SetXY(164, 16);
        $this->Cell(51, 10, $this->data['fecha_y_hora'], 0, 1, 'L');

        // Cliente
        $this->SetXY(40, 44);
        $this->Cell(100, 10, mb_convert_encoding($this->data['cliente'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

        // Domicilio (Nuevo)
        $this->SetXY(50, 50.2); // Posición debajo del nombre del cliente
        $this->Cell(100, 10, mb_convert_encoding($this->data['domicilio'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

        // Concepto (Reducimos el tamaño de letra a 10)
        $this->SetFont('Arial', '', 10);
        $this->SetXY(32, 66);
        $this->MultiCell(120, 8, mb_convert_encoding($this->data['concepto'], 'ISO-8859-1', 'UTF-8'), 0, 'L');

        // Cantidad de meses pagados
        $this->SetXY(15, 66);
        $this->Cell(40, 10, $this->data['cantidad'], 0, 1, 'L');

        // Precio unitario de cada expensa
        $this->SetXY(153, 66);
        $this->Cell(40, 10, "$" . $this->data['precio_unitario'], 0, 1, 'L');

        // Importe
        $this->SetXY(170, 66);
        $this->Cell(30, 10, "$" . number_format($this->data['monto'], 2, ',', '.'), 0, 1, 'R');

        // Monto total
        $this->SetXY(170, 131);
        $this->Cell(30, 10, "$" . number_format($this->data['monto'], 2, ',', '.'), 0, 1, 'R');

        // ✅ Verificar si el QR existe antes de insertarlo en el PDF
        if (!empty($this->data['qr_path']) && file_exists($this->data['qr_path'])) {
            $this->Image($this->data['qr_path'], 180, 25, 20, 20);
        } else {
            // En caso de error, insertar un mensaje de "QR no disponible"
            $this->SetXY(160, 90);
            $this->Cell(30, 10, "QR NO DISPONIBLE", 0, 1, 'C');
        }
    }
}
