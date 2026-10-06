<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Test User',
            'email'    => 'user@example.com',
            'password' => Hash::make('password123'),
            'phone'    => '8888888881',
            'status'   => 'active',
        ]);
    }
}
