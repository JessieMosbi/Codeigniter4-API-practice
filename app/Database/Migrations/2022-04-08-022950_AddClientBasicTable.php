<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClientBasicTable extends Migration
{
	protected $DBGroup = 'member';

	public function up()
	{
		$this->forge->addField([
			'id',
			'name' => [
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null' => false,
				'comment' => '名稱'
			],
			'email' => [
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null' => false,
				'comment' => '信箱'
			],
			'password' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => false,
				'comment' => '密碼'
			],
			'created_at datetime DEFAULT current_timestamp COMMENT \'建立時間\'',
			'updated_at datetime DEFAULT current_timestamp ON update current_timestamp COMMENT \'更新時間\''
		]);

		$this->forge->addUniqueKey('email');

		$this->forge->createTable('client_basic', true, [
			'ENGINE' => 'InnoDB',
			'CHARACTER SET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_unicode_ci',
			'comment' => '客戶端基本資料'
		]);
	}

	public function down()
	{
		$this->forge->dropTable('client_basic', true);
	}
}
