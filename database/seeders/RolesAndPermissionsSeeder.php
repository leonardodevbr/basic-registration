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
            // Módulo: Entregas de Benefícios
            'Entregas de Benefícios' => [
                'view benefit deliveries',
                'create benefit deliveries',
                'update benefit deliveries',
                'delete benefit deliveries',
            ],

            // Módulo: Entregas de Benefícios
            'Unidades' => [
                'view unities',
                'create unities',
                'update unities',
                'delete unities',
            ],

            // Módulo: Usuários
            'Usuários' => [
                'view users',
                'create users',
                'update users',
                'delete users',
            ],

            // Módulo: Permissões e Grupos
            'Permissões e Grupos' => [
                'manage roles and permissions',
            ],

            // Módulo: Perfil Pessoal
            'Perfil Pessoal' => [
                'view own profile',
                'update own profile',
            ],

            // Módulo: Dashboard
            'Dashboard' => [
                'view dashboard',
            ],
        ];

        foreach ($permissions as $module => $actions) {
            foreach ($actions as $permissionName) {
                Permission::firstOrCreate(
                    ['name' => $permissionName],
                    ['guard_name' => 'web', 'module' => $module]
                );
            }
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
