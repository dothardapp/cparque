<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use App\Models\Expensa;
use App\Models\Parcela;
use Filament\Forms;
use Filament\Forms\Components\Builder;
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

class ExpensasRelationManager extends RelationManager
{
    protected static string $relationship = 'expensas';

    protected static ?string $recordTitleAttribute = 'anio';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Select::make('parcela_id')
                    ->label('Parcela')
                    ->options(fn() =>
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
                    ->default(fn ($record) => $this->getOwnerRecord()->id),

                Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('anio')
            ->columns([
                Tables\Columns\TextColumn::make('parcela.descripcion')
                    ->label('Parcela')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('anio')
                    ->label('Año')
                    ->sortable(),

                Tables\Columns\TextColumn::make('mes')
                    ->label('Mes')
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto')
                    ->label('Monto')
                    ->sortable()
                    ->money('ARS'),

                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo')
                    ->sortable()
                    ->money('ARS'),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'pagado parcialmente' => 'Pagado Parcialmente',
                        'pagado' => 'Pagado',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'danger',
                        'pagado parcialmente' => 'warning',
                        'pagado' => 'success',
                    })->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Registrado por'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'pagado parcialmente' => 'Pagado Parcialmente',
                        'pagado' => 'Pagado',
                    ]),
            ])
            ->headerActions([
                Action::make('addExpensa')
                    ->label('Registrar Expensa')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Select::make('parcela_id')
                            ->label('Parcela')
                            ->options(fn() =>
                                $this->getOwnerRecord()?->parcelas()->pluck('descripcion', 'id')->toArray() ?? []
                            )
                            ->searchable()
                            ->required(),
                        TextInput::make('anio')->required(),
                        TextInput::make('mes')->required(),
                        TextInput::make('monto')->required()->numeric(),
                        TextInput::make('saldo')->required()->numeric(),
                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'pagado parcialmente' => 'Pagado Parcialmente',
                                'pagado' => 'Pagado',
                            ])
                            ->required(),
                        Hidden::make('cliente_id'),
                        Hidden::make('user_id')->default(auth()->id()),
                    ])
                    ->action(function (array $data): void {
                        DB::transaction(function () use ($data) {
                            Expensa::create([
                                'parcela_id' => $data['parcela_id'],
                                'cliente_id' => $this->getOwnerRecord()->id,
                                'anio' => $data['anio'],
                                'mes' => $data['mes'],
                                'monto' => $data['monto'],
                                'saldo' => $data['saldo'],
                                'estado' => $data['estado'],
                                'user_id' => auth()->id(),
                            ]);
                        });

                        Notification::make()
                            ->title('Expensa registrada')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
