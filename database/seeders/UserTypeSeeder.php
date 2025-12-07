<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userTypes = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrator with full system access',
                'permissions' => json_encode([
                    'users' => ['create', 'read', 'update', 'delete'],
                    'products' => ['create', 'read', 'update', 'delete'],
                    'categories' => ['create', 'read', 'update', 'delete'],
                    'warehouses' => ['create', 'read', 'update', 'delete'],
                    'stock_in' => ['create', 'read', 'update', 'delete', 'approve'],
                    'stock_out' => ['create', 'read', 'update', 'delete', 'approve'],
                    'reports' => ['view_all']
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Stock Manager',
                'slug' => 'stock_manager',
                'description' => 'Manages products and approves stock transactions',
                'permissions' => json_encode([
                    'products' => ['create', 'read', 'update', 'delete'],
                    'categories' => ['create', 'read', 'update'],
                    'warehouses' => ['read'],
                    'stock_in' => ['read', 'approve', 'reject'],
                    'stock_out' => ['read', 'approve', 'reject'],
                    'reports' => ['view_warehouse']
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Stock Executive',
                'slug' => 'stock_executive',
                'description' => 'Handles stock in and out operations',
                'permissions' => json_encode([
                    'products' => ['read'],
                    'categories' => ['read'],
                    'warehouses' => ['read'],
                    'stock_in' => ['create', 'read', 'update'],
                    'stock_out' => ['create', 'read', 'update'],
                    'reports' => ['view_own']
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('user_types')->insert($userTypes);
    }
}
