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
        'validateInfo' => 'email or password wrong'
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

  public function testApi(): object
  {
    return $this->getEncryptDataForUser('client-1', ['data' => '123']); // TODO: param1 get from database
  }

  /**
   * Create access_token for existing user
   */
  private function getAccessTokenForUser(string $emailAddress, int $responseCode = ResponseInterface::HTTP_OK): object
  {
    try {
      helper('JWT');

      list($JWT) = getSignedJWTForUser($emailAddress);

      return $this->getResponse(
        [
          'status' => 'success',
          'result' => [
            'user' => [
              'email' => $emailAddress
            ],
            'access_token' => $JWT
          ]
        ]
      );
    }
    // JWT exception (include check fail) will be cached here
    catch (Exception $exception) {
      return $this->getResponse(
        [
          'status' => 'fail',
          'message' => $exception->getMessage()
        ],
        ResponseInterface::HTTP_BAD_REQUEST
      );
    }
  }

  private function getEncryptDataForUser(string $clientName, array $data): object
  {
    try {
      list($data, $result) = getEncryptJWTForUser($clientName, $data); // TODO: param1 get from database
      return $this->getResponse(
        [
          'status' => 'success',
          'result' => $data,
          'decode' => $result
        ]
      );
    } catch (Exception $exception) {
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
