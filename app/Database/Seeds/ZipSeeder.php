<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class ZipSeeder extends Seeder
{
  private $faker;

  public function __construct()
  {
    $this->db = \Config\Database::connect('member');
    $this->faker = Factory::create('zh_TW');
  }

  public function run()
  {
    $data = [];
    for ($i = 1; $i <= 5; $i++) {
      $data[] =
        [
          'no' => $this->faker->randomNumber(3, true),
          'city' => '吉城市',
          'district' => $this->faker->name() . '鄉'
        ];
    }
    $this->db->table('zip')->insertBatch($data);
  }
}
