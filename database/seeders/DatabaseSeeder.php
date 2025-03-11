<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@cras.com',
            'password' => bcrypt('senha123'),
        ]);

        Unit::create(['name' => 'CRAS Cafarnaum', 'city' => 'Cafarnaum-BA']);
        Unit::create(['name' => 'CRAS Morro do Chapéu', 'city' => 'Morro do Chapéu-BA']);
    }
}
