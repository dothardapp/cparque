<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Crear permisos
        $permissions = [
            'ver caja movimientos',
            'crear caja movimientos',
            'editar caja movimientos',
            'eliminar caja movimientos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);

        $empleadoRole = Role::firstOrCreate(['name' => 'empleado']);
        $empleadoRole->givePermissionTo(['ver caja movimientos']);

        // Asignar rol a un usuario
        $admin = User::first(); // Asigna el primer usuario como admin
        $admin->assignRole('admin');
        }
}
