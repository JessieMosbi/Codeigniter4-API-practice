<?php

namespace Traits {
    /**
     * Provides common methods to generate fake data when exec the seeder.
     */
    trait SeedTrait
    {
        /**
         * Get the PK.id in specific Table when generate FK.id.
         * @param $tableName
         * @return int|void
         */
        public function getTableRandomId($tableName = null)
        {
            if (!$tableName) {
                return;
            }
            $id = 0;

            $builder = $this->db->table($tableName);
            $randomTupleIndex = $this->faker->numberBetween(0, $builder->countAllResults() - 1);
            $query = $builder->get();
            foreach ($query->getResult() as $index => $row) {
                if ($index === $randomTupleIndex) {
                    $id = $row->id;
                    break;
                }
            }

            return $id;
        }
    }
}
