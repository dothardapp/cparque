## **Relation Manager: Inhumados en ClienteResource**

Este documento detalla la implementación de la relación `inhumados` en el recurso `ClienteResource`.

### **1. Creación del Relation Manager**

Ejecutar el siguiente comando:

```bash
php artisan make:filament-relation-manager ClienteResource inhumados nombre
```

Esto genera un administrador de relación en:

```
app/Filament/Resources/ClienteResource/RelationManagers/InhumadosRelationManager.php
```

### **2. Implementación en el Relation Manager**

Editar `InhumadosRelationManager.php` con la siguiente configuración:

```php
class InhumadosRelationManager extends RelationManager
{
    protected static string $relationship = 'inhumados';
    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('nombre')->required()->maxLength(255),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->sortable()->searchable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
```

### **3. Configuración en el Modelo Cliente**

Agregar la relación en `app/Models/Cliente.php`:

```php
public function inhumados()
{
    return $this->hasMany(Inhumado::class);
}
```

### **4. Integración en ClienteResource**

Añadir en `ClienteResource.php`:

```php
public static function getRelations(): array
{
    return [
        InhumadosRelationManager::class,
    ];
}
```

### **5. Funcionalidad**

-   La relación `inhumados` permite gestionar registros desde el panel de Filament.
-   Se pueden crear, editar y eliminar registros de `inhumados` directamente desde `ClienteResource`.
