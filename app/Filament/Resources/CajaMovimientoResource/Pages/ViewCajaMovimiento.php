<?PHP

namespace App\Filament\Resources\CajaMovimientoResource\Pages;

use App\Actions\ViewReciboPDFAction;
use App\Filament\Resources\CajaMovimientoResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;

class ViewCajaMovimiento extends ViewRecord
{
    protected static string $resource = CajaMovimientoResource::class;

    public function getTitle(): string
    {
        return "Movimiento N° {$this->record->numero_recibo} - {$this->record->cliente->nombre} {$this->record->cliente->apellido}";
    }

    public function getHeaderActions(): array
    {
        return [
            ViewReciboPDFAction::make($this->record->id),
        ];
    }

    public function getInfolist(string $name): ?Infolist
    {
        return Infolist::make()
            ->record($this->getRecord()) // ✅ SE AGREGA ESTO PARA ASOCIAR EL REGISTRO
            ->schema([
                Section::make('Detalles del Movimiento')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('numero_recibo')
                                    ->label('N° Recibo')
                                    ->icon('heroicon-m-document-text')
                                    ->weight('bold'),

                                TextEntry::make('fecha_y_hora')
                                    ->label('Fecha y Hora')
                                    ->dateTime(),

                                TextEntry::make('tipo')
                                    ->label('Tipo de Movimiento')
                                    ->badge()
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'pago_expensas'  => 'Expensas',
                                        'pago_plan_pago' => 'Plan de Pago',
                                        'venta_parcela'  => 'Venta de Parcela',
                                        'otro_ingreso'   => 'Otro Ingreso',
                                        'retiro_fondos'  => 'Retiro de Fondos',
                                        'otro_egreso'    => 'Otro Egreso',
                                        default          => ucfirst($state),
                                    })
                                    ->color(fn($state) => match ($state) {
                                        'pago_expensas'  => 'success',
                                        'pago_plan_pago' => 'success',
                                        'venta_parcela'  => 'primary',
                                        'otro_ingreso'   => 'gray',
                                        'retiro_fondos'  => 'danger',
                                        'otro_egreso'    => 'danger',
                                        default          => 'gray',
                                    }),

                                TextEntry::make('monto')
                                    ->label('Monto')
                                    ->money('ARS')
                                    ->weight('bold')
                                    ->color('success'),
                            ]),

                        TextEntry::make('concepto')
                            ->label('Concepto')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('medio_pago')
                                    ->label('Medio de Pago')
                                    ->badge(),

                                TextEntry::make('cliente.nombre')
                                    ->label('Cliente'),

                                TextEntry::make('referencia.descripcion')
                                    ->label('Parcela'),
                            ]),
                    ])
            ]);
    }
}
