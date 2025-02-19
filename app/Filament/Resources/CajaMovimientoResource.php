<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CajaMovimientoResource\Pages;
use App\Models\CajaMovimiento;
use App\Models\Cliente;
use App\Models\Expensa;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CajaMovimientoResource extends Resource
{
    protected static ?string $model = CajaMovimiento::class;
    protected static ?string $navigationIcon = 'tabler-cash-register';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?string $navigationLabel = 'Caja';
    protected static ?string $modelLabel = 'Movimientos de Caja';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('numero_recibo')
                    ->label('N° Recibo')
                    ->disabled()
                    ->dehydrated()
                    ->default(fn() => str_pad((CajaMovimiento::max('numero_recibo') ?? 0) + 1, 6, '0', STR_PAD_LEFT))
                    ->afterStateHydrated(
                        fn($state, callable $set, $record) =>
                        $set('numero_recibo', $record?->numero_recibo ?? str_pad((CajaMovimiento::max('numero_recibo') ?? 0) + 1, 6, '0', STR_PAD_LEFT))
                    ),

                Forms\Components\DatePicker::make('fecha_y_hora')
                    ->label('Fecha')
                    ->default(now())
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Select::make('cliente_id')
                    ->label('Cliente')
                    ->options(fn() => Cliente::pluck(DB::raw("CONCAT(apellido, ', ', nombre)"), 'id'))
                    ->searchable()
                    ->reactive()
                    ->required(),

                Forms\Components\Textarea::make('concepto'),

                Forms\Components\TextInput::make('monto')
                    ->label('Monto Total')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->reactive()
                    ->afterStateUpdated(
                        fn(callable $get, callable $set) =>
                        $set('monto', Expensa::whereIn('id', array_filter((array) $get('expensas_pagadas')))->sum('saldo'))
                    ),

                Forms\Components\Select::make('medio_pago')
                    ->label('Medio de Pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'transferencia' => 'Transferencia',
                        'tarjeta' => 'Tarjeta',
                        'otro' => 'Otro',
                    ])
                    ->required(),

                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {

        // Obtener la fecha actual
        $fechaActual = now()->format('d/m/Y');

        // Calcular el total de ingresos del día actual
        $totalDelDia = CajaMovimiento::whereDate('fecha_y_hora', now())
            ->whereIn('tipo', ['pago_expensas', 'pago_plan_pago', 'venta_parcela', 'otro_ingreso'])
            ->sum('monto');

        return $table
            ->heading("Caja del Día - $fechaActual | Total: $" . number_format($totalDelDia, 2, ',', '.'))
            ->columns([
                TextColumn::make('numero_recibo')
                    ->label('N° Recibo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fecha_y_hora')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pago_expensas'  => 'Expensas',
                        'pago_plan_pago' => 'Plan de Pago',
                        'venta_parcela'  => 'Venta de Parcela',
                        'otro_ingreso'   => 'Otro Ingreso',
                        'retiro_fondos'  => 'Retiro de Fondos',
                        'otro_egreso'    => 'Otro Egreso',
                        default          => ucfirst($state),
                    }) // Convierte los valores de la BD en nombres más amigables
                    ->color(fn($state) => match ($state) {
                        'pago_expensas'  => 'success',
                        'pago_plan_pago' => 'success',
                        'venta_parcela'  => 'primary',
                        'otro_ingreso'   => 'gray',
                        'retiro_fondos'  => 'danger',
                        'otro_egreso'    => 'danger',
                        default          => 'gray',
                    }),

                /*TextColumn::make('concepto')
                    ->label('Concepto')
                    ->searchable()
                    ->limit(15), // Para evitar columnas muy anchas*/

                TextColumn::make('monto')
                    ->label('Monto')
                    ->sortable()
                    ->money('ARS'),

                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable(),

                TextColumn::make('referencia.descripcion')
                    ->label('Parcela')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'pago_expensas' => 'Expensas',     // Pago normal de expensas
                        'pago_plan_pago' => 'Palnes',    // Pago de expensas bajo plan de pagos
                        'venta_parcela' => 'Venta de Parcelas',     // Venta de una parcela
                        'otro_ingreso' => 'Varios',      // Otro tipo de ingreso
                        'retiro_fondos' => 'Retiro de fondos',     // Retiro de dinero o pago de proveedor
                        'otro_egreso' => 'Egresos'         // Cualquier otro egreso
                    ]),
                Filter::make('fecha_hoy')
                    ->label('Hoy')
                    ->query(fn($query) => $query->whereDate('fecha_y_hora', now()))
                    ->default(), // 🔥 Aplica el filtro por defecto
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('fecha_y_hora', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCajaMovimientos::route('/'),
            'create' => Pages\CreateCajaMovimiento::route('/create'),
            'edit' => Pages\EditCajaMovimiento::route('/{record}/edit'),
            'view' => Pages\ViewCajaMovimiento::route('/{record}'), // Nueva página de vista
        ];
    }



    public static function canCreate(): bool
    {
        $user = Auth::user();

        // Solo permitir a los usuarios con rol "admin" crear nuevos movimientos
        return $user && $user->hasRole('admin');
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();

        // Solo permitir a los usuarios con rol "admin" editar movimientos existentes
        return $user && $user->hasRole('admin');
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();

        // Solo los admins pueden borrar
        return $user && $user->hasRole('admin');
    }
}
