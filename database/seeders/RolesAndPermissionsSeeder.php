<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Limpa cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissões principais
        $permissions = [
            // Entregas
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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Role: Admin (todas as permissões)
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions(Permission::all());

        // Role: Colaborador (acesso limitado)
        $colaborador = Role::firstOrCreate(['name' => 'Colaborador']);
        $colaborador->syncPermissions([
            'view benefit deliveries',
            'create benefit deliveries',
            'update benefit deliveries',
            'view own profile',
            'update own profile',
        ]);
    }
}
