<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Expensa;
use App\PDF\ClienteReporteExpensasPDF;
use Carbon\Carbon;

class ClienteReporteExpensasController extends Controller
{
    public function generarReporte($id)
    {
        // Obtener los datos del cliente
        $cliente = Cliente::with('contactos')->findOrFail($id);

        // Obtener expensas del cliente, ordenadas por año y mes
        $expensas = Expensa::where('cliente_id', $id)
            ->orderBy('anio', 'asc')
            ->get();

        // Obtener domicilio
        $domicilio = optional($cliente->contactos->where('principal', true)->first())->domicilio
            ?? optional($cliente->contactos->first())->domicilio
            ?? 'Sin domicilio - ACTUALIZAR';

        // Datos para el PDF
        $data = [
            'nombre'    => mb_strtoupper($cliente->nombre . ' ' . $cliente->apellido, 'UTF-8'),
            'dni'       => $cliente->dni,
            'domicilio' => mb_strtoupper($domicilio, 'UTF-8'),
            'fecha'     => Carbon::now()->format('d/m/Y H:i'),
            'expensas'  => $expensas, // ✅ Se pasa la lista de expensas al PDF
        ];

        // Generar PDF
        $pdf = new ClienteReporteExpensasPDF($data);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->generarTabla(); // ✅ Llama a la función para imprimir la tabla de expensas

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Reporte_Expensas_Cliente_' . $cliente->dni . '.pdf"');
    }
}
