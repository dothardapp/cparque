<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CajaMovimientoPDFController;
use App\Http\Controllers\ReciboQRController;
use Filament\Http\Middleware\Authenticate; // Middleware de autenticación de Filament

// Ruta pública de Laravel (Pantalla de bienvenida)
Route::get('/', function () {
    return view('welcome');
});

// Grupo de rutas protegido dentro de Filament
Route::middleware(['web', Authenticate::class])->group(function () {
    Route::get('/admin/caja-movimiento/{record}/pdf', [CajaMovimientoPDFController::class, 'generarPDF'])
        ->name('caja-movimiento.pdf');
});

// ✅ Nueva Ruta para Validar Recibo con Código QR (Pública)
Route::get('/validar-recibo/{codigo}', [ReciboQRController::class, 'validar'])->name('validar.recibo');
