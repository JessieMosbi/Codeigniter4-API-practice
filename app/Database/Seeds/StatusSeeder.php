<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;
use Traits\SeedTrait;

/**
 * Generate fake data of status in member database.
 */
class StatusSeeder extends Seeder
{
    use SeedTrait;

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
        $this->db->table('status')->insertBatch([
          [
            'id' => 1,
            'name' => '使用中'
          ],
          [
            'id' => 2,
            'name' => '停權'
          ]
        ]);
    }
}
