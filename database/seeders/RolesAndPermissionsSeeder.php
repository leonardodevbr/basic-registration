<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Limpa cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissões principais
        $permissions = [
            // Entregas de benefícios
            'view benefit deliveries',
            'create benefit deliveries',
            'update benefit deliveries',
            'delete benefit deliveries',

            // Usuários
            'view users',
            'create users',
            'update users',
            'delete users',

            // Permissões e grupos
            'manage roles and permissions',

            // Perfil pessoal
            'view own profile',
            'update own profile',

            // Dashboard
            'view dashboard'
        ];

        $unitId = 1;

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        Role::firstOrCreate(['name' => 'SuperAdmin']);

        // Role: Colaborador (acesso limitado)
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions([
            'view benefit deliveries',
            'create benefit deliveries',
            'update benefit deliveries',
            'delete benefit deliveries',
            'view users',
            'create users',
            'update users',
            'delete users',
            'view own profile',
            'update own profile',
            'view dashboard'
        ]);

        // Role: Colaborador (acesso limitado)
        $colaborador = Role::firstOrCreate(['name' => 'Colaborador']);
        $colaborador->syncPermissions([
            'view dashboard',
            'view benefit deliveries',
            'create benefit deliveries',
            'update benefit deliveries',
            'view own profile',
            'update own profile',
        ]);
    }
}
