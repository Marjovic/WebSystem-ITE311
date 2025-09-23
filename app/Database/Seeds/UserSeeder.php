<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin user
        $this->db->table(tableName: 'users')->insert(set: [
            'name'        => 'Marjovic Prato Alejado',
            'email'       => 'marjovic_alejado@lms.com',
            'password'    => password_hash(password: 'admin123', algo: PASSWORD_DEFAULT),
            'role'        => 'admin',
            'created_at'  => date(format: 'Y-m-d H:i:s'),
            'updated_at'  => date(format: 'Y-m-d H:i:s'),
        ]);

        // Teacher users 
        $teachers = [
            [
                'name'        => 'Kristoff Jet Alejado Rivera',
                'email'       => 'kristoff.rivera@lms.com',
                'password'    => password_hash(password: 'teacher123', algo: PASSWORD_DEFAULT),
                'role'        => 'teacher',
                'created_at'  => date(format: 'Y-m-d H:i:s'),
                'updated_at'  => date(format: 'Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Cyryll Joy Alejado Macalanda',
                'email'       => 'cyryll.macalanda@lms.com',
                'password'    => password_hash(password: 'teacher123', algo: PASSWORD_DEFAULT),
                'role'        => 'teacher',
                'created_at'  => date(format: 'Y-m-d H:i:s'),
                'updated_at'  => date(format: 'Y-m-d H:i:s'),
            ],
        ];

        foreach ($teachers as $teacher) {
            $this->db->table(tableName: 'users')->insert(set: $teacher);
        }

        // Student users
        $students = [
            [
                'name'        => 'Victor Tabaniera Alejado',
                'email'       => 'victor.alejado@student.lms.com',
                'password'    => password_hash(password: 'student123', algo: PASSWORD_DEFAULT),
                'role'        => 'student',
                'created_at'  => date(format: 'Y-m-d H:i:s'),
                'updated_at'  => date(format: 'Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Virginia Prato Alejado',
                'email'       => 'virginia.alejado@student.lms.com',
                'password'    => password_hash(password: 'student123', algo: PASSWORD_DEFAULT),
                'role'        => 'student',
                'created_at'  => date(format: 'Y-m-d H:i:s'),
                'updated_at'  => date(format: 'Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Mary Joy Prato Alejado',
                'email'       => 'maryjoy.alejado@student.lms.com',
                'password'    => password_hash(password: 'student123', algo: PASSWORD_DEFAULT),
                'role'        => 'student',
                'created_at'  => date(format: 'Y-m-d H:i:s'),
                'updated_at'  => date(format: 'Y-m-d H:i:s'),
            ],
        ];

        foreach ($students as $student) {
            $this->db->table(tableName: 'users')->insert(set: $student);
        }
    }
}