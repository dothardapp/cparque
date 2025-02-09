<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use App\Models\Ctacte;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;

class CuentasCorrientesRelationManager extends RelationManager
{
    protected static string $relationship = 'cuentasCorrientes';

    protected static ?string $recordTitleAttribute = 'id';

    // =========================
    // Formulario de creación / edición
    // =========================

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cliente_id')
                    ->label('Cliente')
                    ->options(fn() => $this->getOwnerRecord()
                        ?->pluck('nombre', 'id')->toArray() ?? []
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('fecha')
                    ->label('Fecha')
                    ->default(now())
                    ->required(),

                Forms\Components\Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'EX' => 'EX',
                        'CU' => 'CU',
                        'EXP' => 'EXP',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('debe')
                    ->label('Debe')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\TextInput::make('haber')
                    ->label('Haber')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\Select::make('anio')
                    ->label('Año')
                    ->options(array_combine(range(date('Y'), date('Y') - 10), range(date('Y'), date('Y') - 10)))
                    ->required(),

                Forms\Components\Select::make('mes')
                    ->label('Mes')
                    ->options([
                        '01' => 'Enero',
                        '02' => 'Febrero',
                        '03' => 'Marzo',
                        '04' => 'Abril',
                        '05' => 'Mayo',
                        '06' => 'Junio',
                        '07' => 'Julio',
                        '08' => 'Agosto',
                        '09' => 'Septiembre',
                        '10' => 'Octubre',
                        '11' => 'Noviembre',
                        '12' => 'Diciembre',
                    ])
                    ->required(),

                Forms\Components\Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'Pagado' => 'Pagado',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('recibo')
                    ->label('Recibo')
                    ->maxLength(50),
            ]);
    }

    // =========================
    // Tabla que muestra las cuentas corrientes del cliente
    // =========================

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->label('Fecha')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->sortable(),

                Tables\Columns\TextColumn::make('debe')
                    ->label('Debe')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('haber')
                    ->label('Haber')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('anio')
                    ->label('Año')
                    ->sortable(),

                Tables\Columns\TextColumn::make('mes')
                    ->label('Mes')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Pendiente' => 'warning',
                        'Pagado' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('recibo')
                    ->label('Recibo')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'EX' => 'Expensas EX',
                        'CU' => 'Cuotas CU',
                        'EXP' => 'Expensas EXP',
                    ])
                    ->default(null) // Para que por defecto no filtre nada
            ])

            ->headerActions([
                Action::make('addMovimiento')
                    ->label('Agregar Movimiento')
                    ->icon('heroicon-o-plus')
                    ->form([
                        DatePicker::make('fecha')
                            ->label('Fecha')
                            ->default(now())
                            ->required(),

                        Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'EX' => 'EX',
                                'CU' => 'CU',
                                'EXP' => 'EXP',
                            ])
                            ->required(),

                        TextInput::make('debe')
                            ->label('Debe')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('haber')
                            ->label('Haber')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Select::make('anio')
                            ->label('Año')
                            ->options(array_combine(range(date('Y'), date('Y') - 10), range(date('Y'), date('Y') - 10)))
                            ->required(),

                        Select::make('mes')
                            ->label('Mes')
                            ->options([
                            '01' => 'Enero',
                            '02' => 'Febrero',
                            '03' => 'Marzo',
                            '04' => 'Abril',
                            '05' => 'Mayo',
                            '06' => 'Junio',
                            '07' => 'Julio',
                            '08' => 'Agosto',
                            '09' => 'Septiembre',
                            '10' => 'Octubre',
                            '11' => 'Noviembre',
                            '12' => 'Diciembre',
                            ])
                            ->required(),

                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'Pagado' => 'Pagado',
                            ])
                            ->required(),

                        TextInput::make('recibo')
                            ->label('Recibo')
                            ->maxLength(50),

                        Hidden::make('cliente_id')
                            ->default(fn() => $this->getOwnerRecord()->id),
                    ])
                    ->action(function (array $data): void {
                        DB::transaction(function () use ($data) {
                            Ctacte::create([
                                'cliente_id' => $data['cliente_id'],
                                'fecha'      => $data['fecha'],
                                'tipo'       => $data['tipo'],
                                'debe'       => $data['debe'],
                                'haber'      => $data['haber'],
                                'anio'       => $data['anio'],
                                'mes'        => $data['mes'],
                                'estado'     => $data['estado'],
                                'recibo'     => $data['recibo'],
                            ]);
                        });

                        Notification::make()
                            ->title('Movimiento registrado')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Editar Movimiento')
                    ->form(fn($record) => [
                        DatePicker::make('fecha')
                            ->label('Fecha')
                            ->default(now())
                            ->required(),

                        Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'EX' => 'EX',
                                'CU' => 'CU',
                                'EXP' => 'EXP',
                            ])
                            ->required(),

                        TextInput::make('debe')
                            ->label('Debe')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        TextInput::make('haber')
                            ->label('Haber')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Select::make('anio')
                            ->label('Año')
                            ->options(array_combine(range(date('Y'), date('Y') - 10), range(date('Y'), date('Y') - 10)))
                            ->required(),

                        Select::make('mes')
                            ->label('Mes')
                            ->options([
                                '01' => 'Enero',
                                '02' => 'Febrero',
                                '03' => 'Marzo',
                                '04' => 'Abril',
                                '05' => 'Mayo',
                                '06' => 'Junio',
                                '07' => 'Julio',
                                '08' => 'Agosto',
                                '09' => 'Septiembre',
                                '10' => 'Octubre',
                                '11' => 'Noviembre',
                                '12' => 'Diciembre',
                            ])
                            ->required(),

                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'Pagado' => 'Pagado',
                            ])
                            ->required(),

                        TextInput::make('recibo')
                            ->label('Recibo')
                            ->maxLength(50),
                    ]),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
