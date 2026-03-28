<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username'    => 'admin',
            'email'       => 'admin@copymart.com',
            'password'    => Hash::make('admin1234'),
            'full_name'   => 'Administrador',
            'rol'         => 'administrador',
            'department'  => 'administracion',
            'is_active'   => true,
            'permissions' => [],
        ]);
    }
}
