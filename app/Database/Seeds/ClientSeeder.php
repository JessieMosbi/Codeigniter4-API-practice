<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class ClientSeeder extends Seeder
{
	private $faker;

	public function __construct()
	{
		$this->db = \Config\Database::connect('member');
		$this->faker = Factory::create('zh_TW');
	}

	public function run()
	{
		$password = '123456';
		$data = [];
		for ($i = 0; $i < 50; $i++) {
			array_push($data, [
				'name' => $this->faker->company,
				'email' => $this->faker->email,
				'password' => password_hash($password, PASSWORD_DEFAULT)
			]);
		}
		$this->db->table('client_basic')->insertBatch($data);
	}
}
