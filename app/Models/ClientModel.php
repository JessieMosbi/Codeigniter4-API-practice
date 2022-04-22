<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

/**
 * Model for client_basic Table.
 */
class ClientModel extends Model
{
    /**
     * @var string
     */
    protected $DBGroup = 'default';

    /**
     * @var string
     */
    protected $table = 'client_basic';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    protected $useAutoIncrement = true;

    /**
     * @var string
     */
    protected $returnType = 'object';

    /**
     * @var bool
     */
    protected $useSoftDeletes = false;

    /**
     * @var string[]
     */
    protected $allowedFields = ['name', 'email', 'password', 'avatar'];

    /**
     * @var bool
     */
    protected $useTimestamps = true;

    /**
     * @var string
     */
    protected $createdField = 'created_at';

    /**
     * @var string
     */
    protected $updatedField = 'updated_at';

    /**
     * @var string[]
     */
    protected $beforeInsert = ['getUpdatedDataWithHashedPassword'];

    /**
     * @var string[]
     */
    protected $beforeUpdate = ['getUpdatedDataWithHashedPassword'];

    /**
     * Update the password value in data array.
     *
     * @param array $data
     * @return array
     */
    protected function getUpdatedDataWithHashedPassword(array $data): array
    {
        if (isset($data['data']['password'])) {
            $plaintextPassword = $data['data']['password'];
            $data['data']['password'] = $this->hashPassword($plaintextPassword);
        }
        return $data;
    }

    /**
     * Hash the password.
     *
     * @param string $plaintextPassword
     * @return string
     */
    private function hashPassword(string $plaintextPassword): string
    {
        return password_hash($plaintextPassword, PASSWORD_DEFAULT);
    }
}
