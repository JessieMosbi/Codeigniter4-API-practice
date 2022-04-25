<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * Class Auth
 *
 * Auth provide basic authentication methods.
 */
class Auth extends ApiController
{
    /**
     * Login authentication
     *
     * Get email and password from http request.
     * Params are in request body as 'application/json' type.
     *
     * @return object
     */
    public function login(): object
    {
        $rules = [
            'email'  => [
                'label' => 'email',
                'rules' => 'required|min_length[6]|max_length[50]|valid_email'
            ],
            'password'  => [
                'label' => 'password',
                'rules' => 'required|min_length[8]|max_length[100]|validateInfo[email, password]',
                'errors' => [
                    'validateInfo' => 'email or password wrong'
                ]
            ]
        ];

        $input = $this->getRequestInput($this->request, 'json');

        if (!$this->validateRequest($input, $rules)) {
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
     * API for testing purpose
     *
     * Will be overridden by truly API in the future.
     *
     * @return object
     */
    public function testApi(): object
    {
        $clientName = 'client-1'; // TODO: get from database
        $fakeData = ['data' => '123'];
        if ($this->request->isAJAX()) {
            return $this->getDataForBrowser($clientName, $fakeData);
        } else {
            return $this->getEncryptDataForUser($clientName, $fakeData);
        }
    }


    /**
     * Create access_token for existing user.
     *
     * The access_token is Signed JWT (JWS).
     *
     * @param string $emailAddress
     * @param int $responseCode
     * @return object
     */
    private function getAccessTokenForUser(string $emailAddress, int $responseCode = ResponseInterface::HTTP_OK): object
    {
        try {
            helper('JWT');

            list($JWT) = getSignedJWTForUser($emailAddress);

            return $this->getResponse([
                'status' => 'success',
                'result' => [
                    'user' => [
                        'email' => $emailAddress
                    ],
                    'access_token' => $JWT
                ]
            ]);
        } catch (Exception $exception) {
            // JWT exception (include check fail) will be cached here
            return $this->getResponse(
                [
                    'status' => 'fail',
                    'message' => $exception->getMessage()
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Get data from database upon client's request.
     *
     * The data is Nested JWT = encrypted JWT (JWE) with signed JWT (JWS) in payload.
     *
     * @param string $clientName
     * @param array $data
     * @return object
     */
    private function getEncryptDataForUser(string $clientName, array $data): object
    {
        try {
            list($data, $result) = getEncryptJWTForUser($clientName, $data);
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

    /**
     * Get data from database upon client's (browser's) request.
     *
     * The data is in JSON format
     *
     * @param string $clientName
     * @param array $data
     * @return object
     */
    private function getDataForBrowser(string $clientName, array $data): object
    {
        try {
            return $this->getResponse(
                [
                    'status' => 'success',
                    'result' => $data
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
