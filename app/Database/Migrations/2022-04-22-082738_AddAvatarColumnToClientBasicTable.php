<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvatarColumnToClientBasicTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('client_basic', [
            'avatar' => [
                'type' => 'VARCHAR',
                'constraint'  => 50,
                'null' => true,
                'comment' => '照片'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('client_basic', 'avatar');
    }
}
