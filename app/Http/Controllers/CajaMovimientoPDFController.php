<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use App\PDF\ReciboPDF;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Color\Color;
use Illuminate\Support\Facades\Storage;

class CajaMovimientoPDFController extends Controller
{
    public function generarPDF($id)
    {
        // Obtener datos del recibo con relaciones
        $movimiento = CajaMovimiento::with(['cliente.contactos', 'referencia'])->findOrFail($id);

        // ✅ Enlace de validación del recibo (URL pública para verificar autenticidad)
        $urlValidacion = route('validar.recibo', ['codigo' => $movimiento->qr_code]);
        
        // ✅ Ruta correcta en `storage/app/public/qrcodes/`
        $qrFilename = 'recibo_' . $movimiento->numero_recibo . '.png';
        $qrPath = 'qrcodes/' . $qrFilename;  // ✅ YA NO INCLUYE "public/"
        $qrStoragePath = storage_path('app/public/' . $qrPath); // ✅ Ruta interna correcta
        $qrPublicPath = public_path('storage/' . $qrPath); // ✅ Ruta accesible desde el navegador

        // ✅ Asegurar que la carpeta de QR Codes existe
        if (!Storage::exists('public/qrcodes')) {
            Storage::makeDirectory('public/qrcodes', 0775, true);
        }

        // ✅ Generar el Código QR
        $qrCode = new QrCode(
            data: $urlValidacion,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0), // Negro
            backgroundColor: new Color(255, 255, 255) // Blanco
        );

        // ✅ Guardar QR en formato PNG
        $writer = new PngWriter();
        $qrImage = $writer->write($qrCode)->getString();
        $saveResult = Storage::disk('public')->put($qrPath, $qrImage); // ✅ NO INCLUYE "public/"

        // ✅ Verificar si se guardó correctamente
        if (!$saveResult) {
            dd("❌ Error al guardar el QR en Storage. Verifica permisos en `storage/app/public/qrcodes/`.");
        }


        // ✅ Extraer detalles desde el concepto
        $detalleParcela = $this->extraerDetalleParcela($movimiento->concepto);
        $mesesPagadosArray = $this->extraerMesesPagadosArray($movimiento->concepto);
        $cantidadMeses = count($mesesPagadosArray); // Contar los meses pagados desde el concepto
        $precioUnitario = $cantidadMeses > 0 ? $movimiento->monto / $cantidadMeses : 0; // Calcular el precio unitario

        // ✅ Obtener el domicilio del cliente
        $domicilio = optional($movimiento->cliente->contactos->where('principal', true)->first())->domicilio
            ?? optional($movimiento->cliente->contactos->first())->domicilio
            ?? 'Domicilio no registrado';

        // ✅ Convertir a mayúsculas el nombre del cliente y el domicilio
        $clienteNombre = mb_strtoupper($movimiento->cliente->nombre . ' ' . $movimiento->cliente->apellido, 'UTF-8');
        $domicilio = mb_strtoupper($domicilio, 'UTF-8');

        // ✅ Datos para el PDF
        $data = [
            'numero_recibo'   => $movimiento->numero_recibo,
            'fecha_y_hora'    => Carbon::parse($movimiento->fecha_y_hora)->format('d/m/Y H:i'),
            'cliente'         => $clienteNombre,
            'domicilio'       => $domicilio,
            'concepto'        => "Pago de expensas - {$detalleParcela} - Meses abonados: " . implode(', ', $mesesPagadosArray),
            'cantidad'        => $cantidadMeses,
            'precio_unitario' => number_format($precioUnitario, 2, ',', '.'),
            'monto'           => $movimiento->monto,
            'medio_pago'      => $movimiento->medio_pago,
            'qr_path'         => file_exists($qrPublicPath) ? $qrPublicPath : null, // ✅ Verifica existencia antes de asignar
        ];

        // ✅ Generar PDF
        $pdf = new ReciboPDF($data);
        $pdf->AddPage();

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Recibo_' . $movimiento->numero_recibo . '.pdf"');
    }

    /**
     * Extrae el detalle de la parcela desde el concepto.
     */
    private function extraerDetalleParcela(string $concepto): string
    {
        preg_match('/Parcela: (.*?) \| Meses:/', $concepto, $matches);
        return $matches[1] ?? 'SIN INFORMACIÓN DE PARCELA';
    }

    /**
     * Extrae la lista de meses abonados desde el concepto.
     */
    private function extraerMesesPagadosArray(string $concepto): array
    {
        // Buscar la parte después de "Meses:"
        $partes = explode('Meses:', $concepto);

        if (isset($partes[1])) {
            $meses = explode(',', trim($partes[1])); // Separar por coma
            return array_map('trim', $meses); // Limpiar espacios en blanco
        }

        return [];
    }
}
