<?php

namespace App\Validations;

use App\Models\ClientModel;
use Exception;

class ClientRules
{
  public function validateInfo(string $str, string $fields, array $data): bool
  {
    try {
      // TODO: 官網有一些 check from 前兩個參數
      // https://codeigniter4.github.io/userguide/libraries/validation.html#creating-custom-rules

      $clientModel = new ClientModel();
      $clientInfo = $clientModel->where('email', $data['email'])->first(); // return Object

      return password_verify($data['password'], $clientInfo->password);
    } catch (Exception $e) {
      return false;
    }
  }
}
