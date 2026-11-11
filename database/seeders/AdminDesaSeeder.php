<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminDesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'admin_desa'],
            ['description' => 'Administrator Kantor Desa']
        );

        $desa = Desa::first();

        User::firstOrCreate(
            ['email' => 'admindesa@desa.com'],
            [
                'name' => 'Admin Desa',
                'username' => 'admindesa',
                'password' => Hash::make('password'),
                'role_id' => $role->id,
                'desa_id' => $desa?->id,
                'is_active' => true,
            ]
        );
    }
}

