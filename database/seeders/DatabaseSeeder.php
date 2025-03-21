<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use App\Models\Benefit;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Cria permissões e roles
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // 2. Cria o usuário admin
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('senha123'),
        ]);

        // 3. Atribui role Admin ao usuário
        $admin->assignRole('Admin');

        $user = User::create([
            'name' => 'Colaborador Teste',
            'email' => 'colab@admin.com',
            'password' => bcrypt('senha123'),
        ]);

        $user->assignRole('Colaborador');

        // 4. Cria unidade
        Unit::create([
            'name' => 'CRAS Cafarnaum',
            'city' => 'Cafarnaum-BA',
        ]);

        // 5. Cria benefício
        Benefit::create([
            'name' => 'Peixe Solidário',
            'description' => 'Entrega de peixes na semana santa',
        ]);
    }
}
