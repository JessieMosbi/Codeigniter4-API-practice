<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Exception;

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

    return $this->getAccessTokenForUser($input['email']);
  }

  /**
   * Create access_token for existing user
   */
  private function getAccessTokenForUser(string $emailAddress, int $responseCode = ResponseInterface::HTTP_OK): object
  {
    try {
      helper('JWT');

      list($JWT) = getSignedJWTForUser($emailAddress);

      // TODO: record token request to DB
      // ...

      return $this->getResponse(
        [
          'status' => 'success',
          'message' => '帳密驗證成功。',
          'result' => [
            'user' => [
              'email' => $emailAddress
            ],
            'access_token' => $JWT
          ]
        ]
      );
    } catch (Exception $exception) { // JWT exception (include check fail) will be cached here
      return $this->getResponse(
        [
          'status' => 'fail',
          'message' => $exception->getMessage()
        ],
        ResponseInterface::HTTP_BAD_REQUEST
      );
    }
  }
}
