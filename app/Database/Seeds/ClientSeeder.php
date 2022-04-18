<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

/**
 * Generate fake data of client in member database.
 */
class ClientSeeder extends Seeder
{
    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->db = \Config\Database::connect('member');
        $this->faker = Factory::create('zh_TW');
    }

    /**
     * @return mixed|void
     */
    public function run()
    {
        $password = '12345678';
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
