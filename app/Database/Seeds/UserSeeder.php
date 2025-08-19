<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {        // Admin user
        $this->db->table('users')->insert([
            'username'    => 'marjovic',
            'email'       => 'marjovic_alejado@lms.com',
            'password'    => password_hash('admin123', PASSWORD_DEFAULT),
            'first_name'  => 'Marjovic',
            'middle_name' => 'Prato',
            'last_name'   => 'Alejado',
            'role'        => 'admin',
            'phone'       => '+639391520886',
            'address'     => 'Buayan, General Santos City, Philippines',
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);        // Instructor users
        $instructors = [
            [
                'username'    => 'kristoff_rivera',
                'email'       => 'kristoff.rivera@lms.com',
                'password'    => password_hash('instructor123', PASSWORD_DEFAULT),
                'first_name'  => 'Kristoff Jet',
                'middle_name' => 'Alejado',
                'last_name'   => 'Rivera',
                'role'        => 'instructor',
                'phone'       => '+639331620803',
                'address'     => 'Buayan, General Santos City, Philippines',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'username'    => 'cyryll_macalanda',
                'email'       => 'cyryll.macalanda@lms.com',
                'password'    => password_hash('instructor123', PASSWORD_DEFAULT),
                'first_name'  => 'Cyryll Joy',
                'middle_name' => 'Alejado',
                'last_name'   => 'Macalanda',
                'role'        => 'instructor',
                'phone'       => '+639381320828',
                'address'     => 'Buayan, General Santos City, Philippines',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($instructors as $instructor) {
            $this->db->table('users')->insert($instructor);
        }        // Student users
        $students = [
            [
                'username'    => 'victor_alejado',
                'email'       => 'victor.alejado@student.lms.com',
                'password'    => password_hash('student123', PASSWORD_DEFAULT),
                'first_name'  => 'Victor',
                'middle_name' => 'Tabaniera',
                'last_name'   => 'Alejado',
                'role'        => 'student',
                'phone'       => '+639391830889',
                'address'     => 'Buayan, General Santos City, Philippines',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'username'    => 'virginia_alejado',
                'email'       => 'virginia.alejado@student.lms.com',
                'password'    => password_hash('student123', PASSWORD_DEFAULT),
                'first_name'  => 'Virginia',
                'middle_name' => 'Prato',
                'last_name'   => 'Alejado',
                'role'        => 'student',
                'phone'       => '+639395520870',
                'address'     => 'Buayan, General Santos City, Philippines',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'username'    => 'maryjoy_alejado',
                'email'       => 'maryjoy.alejado@student.lms.com',
                'password'    => password_hash('student123', PASSWORD_DEFAULT),
                'first_name'  => 'Mary Joy',
                'middle_name' => 'Prato',
                'last_name'   => 'Alejado',
                'role'        => 'student',
                'phone'       => '+639391420831',
                'address'     => 'Buayan, General Santos City, Philippines',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($students as $student) {
            $this->db->table('users')->insert($student);
        }
    }
}