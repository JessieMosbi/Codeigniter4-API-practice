<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZipTable extends Migration
{
    protected $DBGroup = 'member';

    public function up()
    {
        // gives id INT(9) NOT NULL AUTO_INCREMENT (PK)
        $this->forge->addField([
            'id',
            'no' => [
                'type' => 'CHAR',
                'constraint' => 6,
                'comment' => '代號'
            ],
            'city' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'comment' => '縣市'
            ],
            'district' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'comment' => '地區'
            ],
            'created_at datetime DEFAULT current_timestamp COMMENT \'建立時間\'',
            'updated_at datetime DEFAULT current_timestamp ON update current_timestamp COMMENT \'更新時間\''
        ]);

        $this->forge->addUniqueKey('no');
        $this->forge->createTable('zip', true, [
            'ENGINE' => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'comment' => '郵遞區號'
        ]);
    }

    public function down()
    {
        // 本身 dropTable code 就有呼叫 disableForeignKeyChecks 繞過 FK 檢查了
        $this->forge->dropTable('zip', true);
    }
}
