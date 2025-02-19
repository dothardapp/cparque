<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReciboQRController extends Controller
{
    public function validar($codigo)
    {
        // Buscar el recibo por el código QR
        $recibo = CajaMovimiento::where('qr_code', $codigo)->with('cliente')->first();

        // Si el recibo NO existe, mostrar un mensaje de error con ícono rojo ❌
        if (!$recibo) {
            return view('validar_recibo', [
                'estado'        => 'Inválido',
                'mensaje'       => 'El recibo NO es válido o ha sido eliminado.',
                'numero_recibo' => null,
                'cliente'       => null,
                'fecha'         => null,
                'monto'         => null,
                'medio_pago'    => null,
                'valido'        => false, // ✅ Variable de control para la vista
            ]);
        }

        // Si el recibo EXISTE, mostrarlo como válido ✅
        return view('validar_recibo', [
            'estado'        => 'Válido',
            'mensaje'       => 'El recibo ha sido verificado correctamente.',
            'numero_recibo' => $recibo->numero_recibo,
            'cliente'       => mb_strtoupper($recibo->cliente->nombre . ' ' . $recibo->cliente->apellido, 'UTF-8'),
            'fecha'         => Carbon::parse($recibo->fecha_y_hora)->format('d/m/Y H:i'),
            'monto'         => number_format($recibo->monto, 2, ',', '.'),
            'medio_pago'    => ucfirst($recibo->medio_pago),
            'valido'        => true, // ✅ Variable de control para la vista
        ]);
    }
}
