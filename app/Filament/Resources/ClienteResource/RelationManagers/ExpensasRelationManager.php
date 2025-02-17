<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use App\Actions\AddDeudaAction;
use App\Actions\AddExpensaAction;
use App\Actions\RegistrarPagoExpensas;
use App\Models\Expensa;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ExpensasRelationManager extends RelationManager
{
    protected static string $relationship = 'expensas';

    protected static ?string $recordTitleAttribute = 'anio';


    private function getTotalAdeudado(): float
    {
        return Expensa::where('cliente_id', $this->getOwnerRecord()->id)
            ->where('estado', '!=', 'pagado')
            ->sum('saldo');
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Select::make('parcela_id')
                    ->label('Parcela')
                    ->options(
                        fn() =>
                        $this->getOwnerRecord()?->parcelas()->pluck('descripcion', 'id')->toArray() ?? []
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('anio')
                    ->label('Año')
                    ->required()
                    ->numeric(),

                TextInput::make('mes')
                    ->label('Mes')
                    ->required()
                    ->numeric(),

                TextInput::make('monto')
                    ->label('Monto')
                    ->required()
                    ->numeric()
                    ->prefix('ARS '),

                TextInput::make('saldo')
                    ->label('Saldo')
                    ->required()
                    ->numeric()
                    ->prefix('ARS '),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'pagado parcialmente' => 'Pagado Parcialmente',
                        'pagado' => 'Pagado',
                    ])
                    ->required(),

                Hidden::make('cliente_id')
                    ->default(fn($record) => $this->getOwnerRecord()->id),

                Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {

        $totalAdeudado = $this->getTotalAdeudado();

        return $table
            ->heading("Expensas - Total Adeudado: ARS" .number_format($totalAdeudado, 2, ',', '.'))
            ->recordTitleAttribute('anio')
            ->columns([
                Tables\Columns\TextColumn::make('parcela.descripcion')
                    ->label('Parcela')
                    ->searchable(),

                Tables\Columns\TextColumn::make('anio')
                    ->label('Año')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mes')
                    ->label('Mes')
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto')
                    ->label('Monto')
                    ->money('ARS'),

                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo')
                    ->money('ARS'),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'pagado parcialmente' => 'Pagado Parcialmente',
                        'pagado' => 'Pagado',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pendiente' => 'danger',
                        'pagado parcialmente' => 'warning',
                        'pagado' => 'success',
                    }),

                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Registrado por'),
            ])
            ->defaultSort('anio', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('parcela_id')
                    ->label('Parcela')
                    ->options(
                        fn() =>
                        $this->getOwnerRecord()?->parcelas()->pluck('descripcion', 'id')->toArray() ?? []
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'pagado parcialmente' => 'Pagado Parcialmente',
                        'pagado' => 'Pagado',
                    ])
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([

                //AddExpensaAction::make(),
                AddDeudaAction::make(),
                RegistrarPagoExpensas::make(),
            ])
            ->paginated([12, 24, 36, 50, 'all'])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
