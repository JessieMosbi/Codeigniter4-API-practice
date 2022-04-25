<?php

namespace App\Validations;

use Exception;
use Config\Database;

/**
 * Member rules for input validation.
 *
 * If you want to add another custom class rule:
 * 1. Create class file in Validations/
 * 2. Register the class in Config/Validation.php
 */
class MemberRules
{
    /**
     * @param string $str
     * @param string $fields
     * @param array $data
     * @return bool
     */
    public function validateStatusValue(string $str, string $fields, array $data): bool
    {
        try {
            $db = $db ?? Database::connect('member');
            $this->db = &$db;

            $sql = 'SELECT id FROM status WHERE id = :id:';
            $query = $this->db->query($sql, ['id' => $data['status']]);
            return ($query->getNumRows()) ? true : false;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
