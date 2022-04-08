<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Auth extends ApiController
{
  public function login(): object
  {
    // Validation exec in ApiController - validateRequest()
    $rules = [
      'email' => 'required|min_length[6]|max_length[50]|valid_email',
      'password' => 'required|min_length[8]|max_length[100]|validateInfo[email, password]'
    ];

    $errors = [
      'password' => [
        'validateInfo' => '驗證失敗。'
      ]
    ];

    $input = $this->getRequestInput($this->request);

    if (!$this->validateRequest($input, $rules, $errors)) {
      return $this->getResponse(
        [
          'status' => 'fail',
          'message' => $this->validator->getErrors(),
        ],
        ResponseInterface::HTTP_BAD_REQUEST
      );
    }

    // TODO: Replaced by sending JWT token to Client
    return $this->getResponse(
      [
        'status' => 'success',
      ],
      ResponseInterface::HTTP_BAD_REQUEST
    );
  }
}
