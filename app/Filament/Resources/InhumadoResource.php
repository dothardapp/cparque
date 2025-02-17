<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InhumadoResource\Pages;
use App\Models\Inhumado;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class InhumadoResource extends Resource
{
    protected static ?string $model = Inhumado::class;
    protected static ?string $navigationGroup = 'Cementerio';
    protected static ?string $navigationIcon = 'fas-cross';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                /*Forms\Components\TextInput::make('cliente_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('parcela_id')
                    ->required()
                    ->numeric(),*/
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('apellido')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('nivel')
                    ->required(),

                Forms\Components\DatePicker::make('fecha_inhumacion')
                    ->required(),
                    
                Forms\Components\DatePicker::make('fecha_nacimiento'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                /*Tables\Columns\TextColumn::make('cliente_id')
                    ->numeric()
                    ->sortable(),*/

                Tables\Columns\TextColumn::make('parcela.descripcion')
                    ->label('Parcela')
                    ->sortable()
                    ->searchable(),


                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('apellido')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nivel'),


                /*Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->date()
                    ->sortable(),*/

                Tables\Columns\TextColumn::make('fecha_inhumacion')
                    ->date()
                    ->sortable(),

                    Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInhumados::route('/'),
            'create' => Pages\CreateInhumado::route('/create'),
            'view' => Pages\ViewInhumado::route('/{record}'),
            'edit' => Pages\EditInhumado::route('/{record}/edit'),
        ];
    }
}
