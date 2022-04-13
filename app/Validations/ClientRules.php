<?php

namespace App\Validations;

use App\Models\ClientModel;
use Exception;

/**
 * Custom rules for client input validation.
 *
 * If you want to add another custom class rule:
 * 1. Create class file in Validations/
 * 2. Register the class in Config/Validation.php
 */
class ClientRules
{
  /**
   * @param string $str
   * @param string $fields
   * @param array $data
   * @return bool
   */
  public function validateInfo(string $str, string $fields, array $data): bool
  {
    try {
      // TODO: There is some checks for $tsr, $fields
      // https://codeigniter4.github.io/userguide/libraries/validation.html#creating-custom-rules

      $clientModel = new ClientModel();
      $clientInfo = $clientModel->where('email', $data['email'])->first(); // return Object

      return password_verify($data['password'], $clientInfo->password);
    } catch (Exception $e) {
      return false;
    }
  }
}
