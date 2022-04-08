<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class ClientModel extends Model
{
  protected $DBGroup = 'member';

  protected $table = 'client_basic';
  protected $primaryKey = 'id';

  protected $useAutoIncrement = true;

  protected $returnType = 'object';
  protected $useSoftDeletes = false;

  protected $allowedFields = ['name', 'email', 'password'];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';

  protected function beforeInsert(array $data): array
  {
    return $this->getUpdatedDataWithHashedPassword($data);
  }

  protected function beforeUpdate(array $data): array
  {
    return $this->getUpdatedDataWithHashedPassword($data);
  }

  private function getUpdatedDataWithHashedPassword(array $data): array
  {
    if (isset($data['data']['password'])) {
      $plaintextPassword = $data['data']['password'];
      $data['data']['password'] = $this->hashPassword($plaintextPassword);
    }
    return $data;
  }

  private function hashPassword(string $plaintextPassword): string
  {
    return password_hash($plaintextPassword, PASSWORD_DEFAULT);
  }
}
