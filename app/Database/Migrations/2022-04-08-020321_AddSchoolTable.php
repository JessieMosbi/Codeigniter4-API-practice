<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSchoolTable extends Migration
{
    protected $DBGroup = 'member';

    public function up()
    {
        $this->forge->addField([
            'id',
            'no' => [
                'type' => 'CHAR',
                'constraint' => 6,
                'comment' => '代號'
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => false,
                'comment' => '名稱'
            ],
            'zip_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'null' => false,
                'comment' => '郵遞區號代碼'
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'comment' => '電話'
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => false,
                'comment' => '地址'
            ],
            'created_at datetime DEFAULT current_timestamp COMMENT \'建立時間\'',
            'updated_at datetime DEFAULT current_timestamp ON update current_timestamp COMMENT \'更新時間\''
        ]);

        $this->forge->addUniqueKey('no');
        $this->forge->addForeignKey('zip_id', 'zip', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('school', true, [
            'ENGINE' => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'comment' => '學校資料'
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('school', true);
    }
}
