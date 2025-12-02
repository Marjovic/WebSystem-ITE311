<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;
use App\Models\RoleModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();

        // Check if admin user already exists
        $existingAdmin = $userModel->where('email', 'marjovicalejado1232@gmail.com')->first();
        if ($existingAdmin) {
            echo "Admin user already exists. Skipping UserSeeder.\n";
            return;
        }

        // Get Admin role ID
        $adminRole = $roleModel->where('role_name', 'Admin')->first();
        if (!$adminRole) {
            echo "Admin role not found. Please run RoleSeeder first.\n";
            return;
        }

        // Create admin user
        $adminData = [
            'user_code'         => 'ADM-' . date('Ymd') . '-0001',
            'first_name'        => 'Marjovic',
            'middle_name'       => 'Prato',
            'last_name'         => 'Alejado',
            'suffix'            => null,
            'email'             => 'marjovicalejado1232@gmail.com',
            'password'          => 'admin123', // Will be hashed by UserModel
            'role_id'           => $adminRole['id'],
            'is_active'         => 1,
            'email_verified_at' => date('Y-m-d H:i:s'), // Pre-verified
            'last_login'        => null
        ];

        try {
            if ($userModel->insert($adminData)) {
                echo "✓ Admin user created successfully!\n";
                echo "  Email: marjovicalejado1232@gmail.com\n";
                echo "  Password: admin123\n";
                echo "  Name: Marjovic Prato Alejado\n";
                echo "  Status: Email verified\n";
            } else {
                $errors = $userModel->errors();
                echo "✗ Failed to create admin user\n";
                print_r($errors);
            }
        } catch (\Exception $e) {
            echo "✗ Error creating admin user: " . $e->getMessage() . "\n";
        }

        echo "\n====================\n";
        echo "Admin user seeded!\n\n";
    }
}