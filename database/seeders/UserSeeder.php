<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name'       => 'Super Admin',
                'username'   => 'superadmin',
                'password'   => Hash::make('admin123'),
                'role'       => 'super_admin',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Admin Pengadilan',
                'username'   => 'admin',
                'password'   => Hash::make('admin123'),
                'role'       => 'admin',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Pimpinan',
                'username'   => 'pimpinan',
                'password'   => Hash::make('pimpinan123'),
                'role'       => 'pimpinan',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Hakim',
                'username'   => 'hakim',
                'password'   => Hash::make('hakim123'),
                'role'       => 'hakim',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}