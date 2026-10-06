<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        AdminUser::create([
            'name'        => 'Super Admin',
            'email'       => 'superadmin@example.com',
            'password'    => Hash::make('password123'),
            'phone'       => '9999999991',
            'role'        => 'super_admin',
            'status'      => 'active',
            'permissions' => ['all'],
        ]);

        // Normal Admin
        AdminUser::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password123'),
            'phone'    => '9999999992',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        // Manager
        AdminUser::create([
            'name'     => 'Manager User',
            'email'    => 'manager@example.com',
            'password' => Hash::make('password123'),
            'phone'    => '9999999993',
            'role'     => 'manager',
            'status'   => 'active',
        ]);
    }
}
