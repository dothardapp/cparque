<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers\InhumadosRelationManager;
use App\Filament\Resources\ClienteResource\RelationManagers\ParcelasRelationManager;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('dni')
                    ->required()
                    ->maxLength(15)
                    ->label('D.N.I'),

                Forms\Components\TextInput::make('codigo')
                    ->label('Código')
                    ->required()
                    ->numeric() // Asegura que solo acepte números
                    ->default(function () {
                        $penultimoCodigo = \App\Models\Cliente::orderByDesc('codigo')
                            ->skip(1) // Salta el último código
                            ->value('codigo');

                        return $penultimoCodigo ? $penultimoCodigo + 1 : 1; // Si no hay penúltimo, empieza desde 1
                    })
                    ->unique(ignoreRecord: true) // Evita duplicados en la validación de Filament
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('codigo', trim($state))), // Limpia espacios extras


                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('apellido')
                    ->required()
                    ->maxLength(100),

                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->required(),

                // Selección de sector
                Forms\Components\Select::make('sector_id')
                    ->label('Sector')
                    ->relationship('parcelas.lote.sector', 'descripcion')
                    ->preload()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('lote_id', null)),

                // Selección de lote
                Forms\Components\Select::make('lote_id')
                    ->label('Lote')
                    ->options(fn (callable $get) => \App\Models\Lote::where('sector_id', $get('sector_id'))
                        ->pluck('descripcion', 'id'))
                    ->preload()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('parcela_id', null))
                    ->disabled(fn (callable $get) => empty($get('sector_id'))),

                // Selección de parcela
                Forms\Components\Select::make('parcela_id')
                    ->label('Parcela')
                    ->options(
                        fn (callable $get) => ($parcelas = \App\Models\Parcela::where('lote_id', $get('lote_id'))
                            ->where('estado', 'libre')
                            ->pluck('numero', 'id'))
                            ->isNotEmpty() ? $parcelas->toArray() : ['' => '⚠️ No hay parcelas disponibles']
                    )
                    ->preload()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set, $state) => $set('inhumados.*.parcela_id', $state))
                    ->disabled(fn (callable $get) => empty($get('lote_id'))),

                // Posibilidad de asignar o crear un inhumado
                Forms\Components\Repeater::make('inhumados')
                    ->relationship('inhumados')
                    ->label('Inhumados')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->label('Nombre del inhumado')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('apellido')
                            ->required()
                            ->label('Apellido del inhumado')
                            ->maxLength(100),

                        Forms\Components\DatePicker::make('fecha_nacimiento')
                            ->label('Fecha de Nacimiento'),

                        Forms\Components\DatePicker::make('fecha_inhumacion')
                            ->required()
                            ->label('Fecha de Inhumación'),

                        Forms\Components\Select::make('parcela_id')
                            ->relationship('parcela', 'numero')
                            ->required()
                            ->label('Parcela de inhumación')
                            ->preload()
                            ->searchable()
                            ->reactive()
                            ->options(fn (callable $get) => [
                                '' => $get('../../parcela_id')
                                    ? '✅ Parcela asignada: '.\App\Models\Parcela::with('lote.sector')->find($get('../../parcela_id'))->lote->sector->descripcion.
                                    ', Lote: '.\App\Models\Parcela::with('lote.sector')->find($get('../../parcela_id'))->lote->descripcion.
                                    ', Parcela: '.\App\Models\Parcela::with('lote.sector')->find($get('../../parcela_id'))->numero
                                    : '⚠️ No hay parcela seleccionada',
                            ] + \App\Models\Parcela::where('id', $get('../../parcela_id'))->pluck('numero', 'id')->toArray())
                            ->default(fn (callable $get) => $get('../../parcela_id')),
                    ])
                    ->collapsible()
                    ->addActionLabel('Agregar nuevo inhumado'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dni')
                    ->searchable()
                    ->label('D.N.I')
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->label('Código')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('apellido')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('apellido', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            InhumadosRelationManager::class,
            ParcelasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'view' => Pages\ViewCliente::route('/{record}'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
