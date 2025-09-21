<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin user
        $this->db->table('users')->insert([
            'name'        => 'Marjovic Prato Alejado',
            'email'       => 'marjovic_alejado@lms.com',
            'password'    => password_hash('admin123', PASSWORD_DEFAULT),
            'role'        => 'admin',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        // Teacher users (changed from instructor to teacher to match Auth controller)
        $teachers = [
            [
                'name'        => 'Kristoff Jet Alejado Rivera',
                'email'       => 'kristoff.rivera@lms.com',
                'password'    => password_hash('teacher123', PASSWORD_DEFAULT),
                'role'        => 'teacher',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Cyryll Joy Alejado Macalanda',
                'email'       => 'cyryll.macalanda@lms.com',
                'password'    => password_hash('teacher123', PASSWORD_DEFAULT),
                'role'        => 'teacher',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($teachers as $teacher) {
            $this->db->table('users')->insert($teacher);
        }

        // Student users
        $students = [
            [
                'name'        => 'Victor Tabaniera Alejado',
                'email'       => 'victor.alejado@student.lms.com',
                'password'    => password_hash('student123', PASSWORD_DEFAULT),
                'role'        => 'student',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Virginia Prato Alejado',
                'email'       => 'virginia.alejado@student.lms.com',
                'password'    => password_hash('student123', PASSWORD_DEFAULT),
                'role'        => 'student',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Mary Joy Prato Alejado',
                'email'       => 'maryjoy.alejado@student.lms.com',
                'password'    => password_hash('student123', PASSWORD_DEFAULT),
                'role'        => 'student',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($students as $student) {
            $this->db->table('users')->insert($student);
        }
    }
}