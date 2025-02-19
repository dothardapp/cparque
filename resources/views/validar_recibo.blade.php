<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validación de Recibo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="p-4 shadow-lg card" style="max-width: 500px;">

            {{-- ✅ Mostrar mensaje según si el recibo es válido o no --}}
            @if ($valido)
                <div class="text-center">
                    <h4 class="text-success fw-bold">✅ Recibo Válido</h4>
                    <p class="text-muted">{{ $mensaje }}</p>
                </div>
            @else
                <div class="text-center">
                    <h4 class="text-danger fw-bold">❌ Recibo Inválido</h4>
                    <p class="text-muted">{{ $mensaje }}</p>
                </div>
            @endif

            <hr>

            {{-- ✅ Solo mostrar los datos si el recibo es válido --}}
            @if ($valido)
                <div class="mb-3">
                    <h6 class="fw-bold">Número de Recibo:</h6>
                    <p class="text-primary fs-5">{{ $numero_recibo }}</p>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold">Cliente:</h6>
                    <p class="text-uppercase">{{ $cliente }}</p>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold">Fecha:</h6>
                    <p>{{ $fecha }}</p>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold">Monto:</h6>
                    <p class="text-success fs-4 fw-bold">$ {{ $monto }}</p>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold">Medio de Pago:</h6>
                    <p class="badge bg-info text-dark fs-6">{{ $medio_pago }}</p>
                </div>
            @endif

        </div>
    </div>

</body>
</html>
