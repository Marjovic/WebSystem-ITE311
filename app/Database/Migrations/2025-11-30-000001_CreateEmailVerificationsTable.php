<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailVerificationsTable extends Migration
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
                'comment'    => 'Email address to verify',
            ],
            'verification_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
                'comment'    => 'Unique token for email verification',
            ],
            'expires_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'comment' => 'When the verification link expires (typically 24 hours)',
            ],
            'verified_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'When the email was successfully verified',
            ],
            'is_used' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Whether verification token has been used',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'comment'    => 'IP address when verification was requested',
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
        $this->forge->addKey('verification_token');
        $this->forge->addKey('expires_at');
        $this->forge->addKey(['verification_token', 'is_used']); // Composite index for verification
        
        // Foreign key
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('email_verifications');
    }

    public function down()
    {
        $this->forge->dropTable('email_verifications');
    }
}
