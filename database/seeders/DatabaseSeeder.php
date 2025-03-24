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
        $unit = Unit::create([
            'name' => 'CRAS Cafarnaum',
            'city' => 'Cafarnaum-BA',
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => bcrypt('senha123'),
        ]);
        $superAdmin->assignRole('SuperAdmin');

        $admin = User::create([
            'unit_id' => $unit->id,
            'name' => 'Admin Teste',
            'email' => 'admin@admin.com',
            'password' => bcrypt('senha123'),
        ]);
        $admin->assignRole('Admin');

        $user = User::create([
            'unit_id' => $unit->id,
            'name' => 'Colaborador Teste',
            'email' => 'colab@admin.com',
            'password' => bcrypt('senha123'),
        ]);
        $user->assignRole('Colaborador');

        Benefit::create([
            'unit_id' => $unit->id,
            'name' => 'Peixe Solidário',
            'description' => 'Entrega de peixes na semana santa',
        ]);
    }
}
