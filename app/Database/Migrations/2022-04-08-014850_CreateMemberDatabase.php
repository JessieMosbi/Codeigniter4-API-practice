<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMemberDatabase extends Migration
{
	public function up()
	{
		// CREATE DATABASE IF NOT EXISTS member
		// TODO: DB engine、charset???
		$this->forge->createDatabase('member', true);
	}

	public function down()
	{
		$this->forge->dropDatabase('member');
	}
}
