## **Relation Manager: Parcelas en ClienteResource**

Este documento detalla la implementación de la relación `parcelas` en el recurso `ClienteResource`.

### **1. Creación del Relation Manager**

Ejecutar el siguiente comando:

```bash
php artisan make:filament-relation-manager ClienteResource parcelas numero
```

Esto genera un administrador de relación en:

```
app/Filament/Resources/ClienteResource/RelationManagers/ParcelasRelationManager.php
```

### **2. Implementación en el Relation Manager**

Editar `ParcelasRelationManager.php` con la siguiente configuración:

```php
class ParcelasRelationManager extends RelationManager
{
    protected static string $relationship = 'parcelas';
    protected static ?string $recordTitleAttribute = 'numero';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('numero')->required()->maxLength(255),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')->sortable()->searchable(),
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
public function parcelas()
{
    return $this->hasMany(Parcela::class);
}
```

### **4. Integración en ClienteResource**

Añadir en `ClienteResource.php`:

```php
public static function getRelations(): array
{
    return [
        ParcelasRelationManager::class,
    ];
}
```

### **5. Funcionalidad**

-   La relación `parcelas` permite gestionar registros desde el panel de Filament.
-   Se pueden crear, editar y eliminar registros de `parcelas` directamente desde `ClienteResource`.
