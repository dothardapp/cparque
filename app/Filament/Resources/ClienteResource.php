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
    protected static ?string $navigationIcon = 'heroicon-m-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('dni')
                    ->required()
                    ->maxLength(13)
                    ->label('D.N.I'),

                Forms\Components\TextInput::make('codigo')
                    ->label('Código')
                    ->required()
                    ->numeric()
                    ->default(function () {
                        $penultimoCodigo = \App\Models\Cliente::orderByDesc('codigo')
                            ->skip(1)
                            ->value('codigo');
                        return $penultimoCodigo ? $penultimoCodigo + 1 : 1;
                    })
                    ->unique(ignoreRecord: true)
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('codigo', trim($state))),

                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('apellido')
                    ->required()
                    ->maxLength(100),

                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->required(),
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
            ->defaultSort('codigo', 'desc')
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        // Aquí registramos el RelationManager de Inhumados, y si quieres, Parcelas
        return [
            ParcelasRelationManager::class,
            InhumadosRelationManager::class,
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
