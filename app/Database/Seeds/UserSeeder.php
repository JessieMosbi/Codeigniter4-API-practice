<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;
use Traits\SeedTrait;

/**
 * Generate fake data of user in member database.
 */
class UserSeeder extends Seeder
{
    use SeedTrait;

    /**
     * @var \Faker\Generator
     */
    private $faker;
    /**
     * @var string[]
     */
    private $gender = ["男", "女"];

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
        // status table
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

        // user table
        $data = [];
        for ($i = 0; $i < 50; $i++) {
            array_push($data, [
                'name' => $this->faker->name,
                'sex' => $this->gender[$this->faker->numberBetween(0, 1)],
                // 'identification' => $this->faker->regexify('[A-Z]{1}[1-2]{1}[1-9]{8}')
                'identification' => $this->faker->personalIdentityNumber,
                'school_id' => $this->getTableRandomId('school'),
                'status_id' => $this->getTableRandomId('status'),
            ]);
        }
        $this->db->table('user')->insertBatch($data);
    }
}
