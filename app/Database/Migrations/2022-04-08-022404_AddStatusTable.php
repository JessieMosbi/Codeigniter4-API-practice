<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusTable extends Migration
{
    protected $DBGroup = 'member';

    public function up()
    {
        $this->forge->addField([
            'id',
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'comment' => '名稱'
            ],
            'created_at datetime DEFAULT current_timestamp NOT NULL COMMENT \'建立時間\'',
            'updated_at datetime DEFAULT current_timestamp NOT NULL ON update current_timestamp COMMENT \'更新時間\'',
        ]);

        $this->forge->createTable('status', true, [
            'ENGINE' => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'comment' => '使用者狀態一覽'
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('status', true);
    }
}
