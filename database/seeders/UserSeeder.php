<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'user_type_id' => 1, // Assuming 1 corresponds to Admin in UserTypeSeeder
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'user_type_id' => 2, // Assuming 2 corresponds to Stock Manager in UserTypeSeeder
            'name' => 'Stock Manager User',
            'email' => 'stockmanager@test.com',
            'phone' => '0987654321',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'user_type_id' => 3, // Assuming 3 corresponds to Stock Executive in UserTypeSeeder
            'name' => 'Stock Executive User',
            'email' => 'stockexecutive@test.com',
            'phone' => '1122334455',
            'password' => bcrypt('password'),
        ]);
    }
}
