<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOtpVerificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'References users table',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
                'comment'    => 'Email address where OTP was sent',
            ],
            'otp_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'The OTP code (6 digits)',
            ],
            'otp_type' => [
                'type'       => 'ENUM',
                'constraint' => ['login', 'registration', 'password_reset', '2fa', 'email_verification'],
                'default'    => 'login',
                'comment'    => 'Purpose of the OTP',
            ],
            'expires_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => 'When the OTP expires (typically 5-10 minutes)',
            ],
            'verified_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'When the OTP was successfully verified',
            ],
            'attempts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Number of verification attempts',
            ],
            'max_attempts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 3,
                'comment'    => 'Maximum allowed attempts before OTP is invalidated',
            ],
            'is_used' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Whether OTP has been used (prevents reuse)',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);
        
        // Indexes for performance
        $this->forge->addKey('user_id');
        $this->forge->addKey('email');
        $this->forge->addKey('otp_code');
        $this->forge->addKey('expires_at');
        $this->forge->addKey(['otp_code', 'email', 'is_used']); // Composite index for verification
        
        // Foreign key
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('otp_verifications');
    }

    public function down()
    {
        $this->forge->dropTable('otp_verifications');
    }
}
