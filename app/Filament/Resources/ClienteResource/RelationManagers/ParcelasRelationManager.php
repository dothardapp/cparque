<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use App\Models\Parcela;
use App\Models\Sector;
use App\Models\Lote;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;

class ParcelasRelationManager extends RelationManager
{
    protected static string $relationship = 'parcelas';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Parcela N°')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('descripcion')
                    ->searchable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado'),
            ])
            ->filters([])
            ->headerActions([
                Action::make('vincularParcela')
                    ->label('Vincular Parcela')
                    ->icon('heroicon-o-plus')
                    ->form([
                        // 1) Seleccionar Sector
                        Forms\Components\Select::make('sector_id')
                            ->label('Sector')
                            ->options(fn () =>
                                Sector::pluck('descripcion', 'id')
                            )
                            ->reactive()
                            ->searchable()
                            ->afterStateUpdated(fn (callable $set) => $set('lote_id', null))
                            ->dehydrated(false)
                            ->required(),

                        // 2) Seleccionar Lote, filtrado por sector_id
                        Forms\Components\Select::make('lote_id')
                            ->label('Lote')
                            ->options(fn (callable $get) =>
                                Lote::where('sector_id', $get('sector_id'))
                                    ->pluck('descripcion', 'id')
                            )
                            ->reactive()
                            ->searchable()
                            ->afterStateUpdated(fn (callable $set) => $set('parcela_id', null))
                            ->dehydrated(false)
                            ->required(),

                        // 3) Seleccionar Parcela (libre), filtrada por lote_id
                        Forms\Components\Select::make('parcela_id')
                            ->label('Parcela')
                            ->options(fn (callable $get) =>
                                Parcela::where('lote_id', $get('lote_id'))
                                    ->where('estado', 'libre')
                                    ->pluck('descripcion', 'id')
                            )
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        DB::transaction(function () use ($data) {
                            $parcela = Parcela::findOrFail($data['parcela_id']);
                            $parcela->update([
                                'cliente_id' => $this->ownerRecord->id,
                                'estado'     => 'ocupada',
                            ]);
                        });

                        Notification::make()
                            ->title('Parcela vinculada correctamente')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                // Desvincular
                Action::make('desvincular')
                    ->label('Desvincular')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->hidden(fn (Parcela $record) => $record->estado !== 'ocupada')
                    ->action(function (Parcela $record) {
                        DB::transaction(function () use ($record) {
                            $record->update([
                                'cliente_id' => 185000,
                                'estado'     => 'libre',
                            ]);
                        });

                        Notification::make()
                            ->title('Parcela liberada correctamente')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
