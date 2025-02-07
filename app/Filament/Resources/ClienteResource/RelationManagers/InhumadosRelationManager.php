<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use App\Models\Inhumado;
use App\Models\Parcela;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;

class InhumadosRelationManager extends RelationManager
{
    // Debe coincidir con `public function inhumados() { return $this->hasMany(Inhumado::class); }` en tu modelo Cliente

    protected static string $relationship = 'inhumados';

    protected static ?string $recordTitleAttribute = 'nombre';

    // =========================
    // Formulario de creación / edición
    // =========================

    public function form(Forms\Form $form): Forms\Form
    {

        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('apellido')
                    ->label('Apellido')
                    ->maxLength(255),

                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->label('Fecha de Nacimiento')
                    ->default('1970-01-01'),

                Forms\Components\DatePicker::make('fecha_inhumacion')
                    ->label('Fecha de Inhumación')
                    ->required(),

                Forms\Components\Select::make('parcela_id')
                    ->label('Parcela')
                    ->options(function () {
                        // Obtiene el cliente padre
                        $cliente = $this->getOwnerRecord();
                        if (! $cliente) {
                            return [];
                        }

                        // Muestra las parcelas que pertenecen a este cliente
                        return $cliente->parcelas
                            ->pluck('descripcion', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    // =========================
    // Tabla que muestra los inhumados de este cliente
    // =========================

    public function table(Table $table): Table
    {

        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->label('Nombre'),

                Tables\Columns\TextColumn::make('apellido')
                    ->searchable()
                    ->label('Apellido')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->date()
                    ->label('F. Nacimiento')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('fecha_inhumacion')
                    ->date()
                    ->label('F. Inhumación'),

                Tables\Columns\TextColumn::make('nivel')
                    ->label('Nivel'),

                Tables\Columns\TextColumn::make('parcela.descripcion')
                    ->label('Parcela'),

            ])
            ->filters([])
            ->headerActions([
                Action::make('addInhumado')
                    ->label('Crear Inhumado')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\Select::make('parcela_id')
                            ->label('Parcela')
                            ->options(
                                fn() =>
                                $this->getOwnerRecord()?->parcelas()->pluck('descripcion', 'id')->toArray() ?? []
                            )
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) return;

                                $count = \App\Models\Inhumado::where('parcela_id', $state)->count();
                                $niveles = ['primer_nivel', 'segundo_nivel', 'tercer_nivel'];

                                if ($count >= 3) {
                                    $set('nivel', '⚠️ Parcela completa');
                                    $set('parcela_llena', 'true');
                                } else {
                                    $set('nivel', $niveles[$count] ?? 'primer_nivel'); // Se asegura de asignar un valor válido
                                    $set('parcela_llena', 'false');
                                }
                            }),

                        TextInput::make('nivel')->required()
                            ->label('Nivel')
                            ->disabled(),

                        TextInput::make('nombre')->required(),
                        TextInput::make('apellido')->required(),
                        DatePicker::make('fecha_inhumacion')->required(),

                        Hidden::make('parcela_llena'),
                    ])
                    ->action(function (array $data): void {
                        if ($data['parcela_llena'] === 'true') {
                            Notification::make()
                                ->title('Error')
                                ->body('No se puede agregar más inhumados, la parcela está completa.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Si por alguna razón el 'nivel' no viene en el formulario, aseguramos que tenga un valor predeterminado
                        $niveles = ['primer_nivel', 'segundo_nivel', 'tercer_nivel'];
                        $count = \App\Models\Inhumado::where('parcela_id', $data['parcela_id'])->count();
                        $data['nivel'] = $niveles[$count] ?? 'primer_nivel';

                        DB::transaction(function () use ($data) {
                            $parcela = Parcela::findOrFail($data['parcela_id']);
                            $parcela->update([
                                'cliente_id' => $this->ownerRecord->id,
                                'estado'     => 'ocupada',
                            ]);

                            Inhumado::create([
                                'cliente_id'      => $this->ownerRecord->id,
                                'parcela_id'      => $data['parcela_id'],
                                'nivel'           => $data['nivel'], // Ahora siempre tendrá un valor
                                'nombre'          => $data['nombre'],
                                'apellido'        => $data['apellido'],
                                'fecha_inhumacion' => $data['fecha_inhumacion'],
                            ]);
                        });

                        Notification::make()
                            ->title('Inhumado registrado')
                            ->success()
                            ->send();
                    }),

            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
