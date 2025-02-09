<?php

namespace App\Filament\Resources\ClienteResource\RelationManagers;

use App\Models\Contacto;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;

class ContactosRelationManager extends RelationManager
{
    protected static string $relationship = 'contactos';

    protected static ?string $recordTitleAttribute = 'telefono';

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

                Forms\Components\TextInput::make('barrio')
                    ->label('Barrio')
                    ->maxLength(255)
                    ->required(),

                Forms\Components\TextInput::make('domicilio')
                    ->label('Domicilio')
                    ->maxLength(255)
                    ->required(),

                Forms\Components\TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(20)
                    ->required(),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),

                Forms\Components\Textarea::make('comentario')
                    ->label('Comentario')
                    ->maxLength(500)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('principal')
                    ->label('Contacto principal')
                    ->default(false),
            ]);
    }

    // =========================
    // Tabla que muestra los contactos de este cliente
    // =========================

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('telefono')
            ->columns([
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable()
                    ->label('Teléfono'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),

                Tables\Columns\TextColumn::make('barrio')
                    ->searchable()
                    ->label('Barrio'),

                Tables\Columns\TextColumn::make('domicilio')
                    ->searchable()
                    ->label('Domicilio'),

                Tables\Columns\IconColumn::make('principal')
                    ->label('Principal')
                    ->boolean(),
            ])
            ->filters([])
            ->headerActions([
                Action::make('addContacto')
                    ->label('Agregar Contacto')
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->required()
                            ->maxLength(20),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('barrio')
                            ->label('Barrio')
                            ->maxLength(255)
                            ->required(),

                        TextInput::make('domicilio')
                            ->label('Domicilio')
                            ->maxLength(255)
                            ->required(),

                        Toggle::make('principal')
                            ->label('Contacto principal')
                            ->default(false),

                        Hidden::make('cliente_id')
                            ->default(fn() => $this->getOwnerRecord()->id),
                    ])
                    ->action(function (array $data): void {
                        DB::transaction(function () use ($data) {
                            Contacto::create([
                                'cliente_id' => $data['cliente_id'],
                                'telefono'   => $data['telefono'],
                                'email'      => $data['email'],
                                'barrio'     => $data['barrio'],
                                'domicilio'  => $data['domicilio'],
                                'principal'  => $data['principal'],
                            ]);
                        });

                        Notification::make()
                            ->title('Contacto registrado')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Editar Contacto')
                    ->form(fn($record) => [
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->required()
                            ->maxLength(20),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('barrio')
                            ->label('Barrio')
                            ->maxLength(255)
                            ->required(),

                        TextInput::make('domicilio')
                            ->label('Domicilio')
                            ->maxLength(255)
                            ->required(),

                        Toggle::make('principal')
                            ->label('Contacto principal'),
                    ])
                    ->mutateFormDataUsing(function (array $data) {
                        return $data;
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
