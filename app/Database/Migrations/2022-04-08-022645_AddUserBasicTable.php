<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserBasicTable extends Migration
{
    protected $DBGroup = 'member';

    public function up()
    {
        $this->forge->addField([
            'id',
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => '姓名'
            ],
            'sex' => [
                'type' => 'VARCHAR',
                'constraint' => 4,
                'null' => false,
                'comment' => '性別'
            ],
            'identification' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'comment' => '證照號碼'
            ],
            'school_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'null' => false,
                'comment' => '學校代碼'
            ],
            'status_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'null' => false,
                'comment' => '狀態代碼',
            ],
            'created_at datetime DEFAULT current_timestamp NOT NULL COMMENT \'建立時間\'',
            'updated_at datetime DEFAULT current_timestamp NOT NULL ON update current_timestamp COMMENT \'更新時間\''
        ]);

        $this->forge->addUniqueKey('identification');
        $this->forge->addForeignKey('school_id', 'school', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('status_id', 'status', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('user_basic', true, [
            'ENGINE' => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'comment' => '使用者基本資料'
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('user_basic', true);
    }
}
