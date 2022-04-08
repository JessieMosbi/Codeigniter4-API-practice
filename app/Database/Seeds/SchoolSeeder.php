<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;
use Traits\SeedTrait;

class SchoolSeeder extends Seeder
{
  use SeedTrait;

  private $faker;
  private $phase = ["國小", "國中"];

  public function __construct()
  {
    $this->db = \Config\Database::connect('member');
    $this->faker = Factory::create('zh_TW');
  }

  public function run()
  {
    $randomSchoolTypeIndex = $this->faker->numberBetween(0, 1);
    $data = [];
    for ($i = 1; $i <= 5; $i++) {

      // school.no 不能重複
      $pass = false;
      $builder = $this->db->table('school');
      while (!$pass) {
        $no = $this->faker->numerify('99####');
        $builder->where('no', $no);

        if (!$builder->get()->getNumRows()) {
          $data =
            [
              'no' => $no,
              'name' => $this->faker->name() . $this->phase[$randomSchoolTypeIndex],
              'phone' => $this->faker->phoneNumber(),
              'address' => $this->faker->address(),
              'zip_id' => $this->getTableRandomId('zip'),
            ];
          $this->db->table('school')->insert($data);
          $pass = true;
        }
      }
    }
  }
}
