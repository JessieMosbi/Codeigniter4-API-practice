<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use App\Models\ClientModel;

/**
 * Class Auth
 *
 * Auth provide basic authentication methods.
 */
class Auth extends ApiController
{
    public function __construct()
    {
        $this->clientModel = new ClientModel();
    }

    /**
     * Insert a tuple in client_basic Table
     *
     * Params are 'multipart/form-data content' (form with file) type
     *
     * @return object
     */
    public function createClient(): object
    {
        $rules = [
            'name'  => [
                'label' => 'name',
                'rules' => 'required|max_length[100]',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],
            'email'  => [
                'label' => 'email',
                'rules' => 'required|valid_email|is_unique[client_basic.email]',
                'errors' => [
                    'required' => '{field} is required',
                    'valid_email' => '{field} is in invalid format',
                    'is_unique' => '{field} is duplicated'
                ]
            ],
            'password'  => [
                'label' => 'password',
                'rules' => 'required|min_length[8]|max_length[100]',
                'errors' => [
                    'required' => '{field} is required'
                ]
            ],
            'passconf'  => [
                'label' => 'confirmed password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => '{field} is required'
                ]
            ],
            'avatar'  => [
                'label' => 'avatar',
                'rules' => 'permit_empty|uploaded[avatar]|max_size[avatar,2048]|is_image[avatar]',
                'errors' => [
                    'uploaded' => '{field} is required'
                ]
            ],
        ];

        try {
            $input = $this->getRequestInput($this->request, 'form');
            if (!$this->validateRequest($input, $rules)) {
                return $this->getResponse(
                    [
                        'status' => 'fail',
                        'message' => $this->validator->getErrors(),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST
                );
            }

            $data = [
                'name' => $this->request->getVar('name'),
                'email' => $this->request->getVar('email'),
                'password' => $this->request->getVar('password')
            ];

            if ($file = $this->request->getFile('avatar')) {
                if (!$file->isValid()) {
                    throw new Exception($file->getErrorString() . '(' . $file->getError() . ')');
                }
                $filePath = $file->store('/avatar/');
                $fileName = str_replace('/avatar/', '', $filePath);
                $data['avatar'] = $fileName;
            }
            $this->clientModel->save($data);

            return $this->getResponse(
                [
                    'status' => 'success'
                ],
            );
        } catch (Exception $e) {
            return $this->getResponse(
                [
                    'status' => 'fail',
                    'message' => $e->getMessage()
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }
    }

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

        return $this->getAccessTokenForClient($input['email']);
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
            return $this->getDataForClient($clientName, $fakeData);
        } else {
            return $this->getEncryptDataForClient($clientName, $fakeData);
        }
    }


    /**
     * Create access_token for existing client.
     *
     * The access_token is Signed JWT (JWS).
     *
     * @param string $emailAddress
     * @param int $responseCode
     * @return object
     */
    private function getAccessTokenForClient(
        string $emailAddress,
        int $responseCode = ResponseInterface::HTTP_OK
    ): object {
        try {
            helper('JWT');

            list($JWT) = generateSignedJWT($emailAddress);

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
    private function getEncryptDataForClient(string $clientName, array $data): object
    {
        try {
            list($data, $result) = getEncryptJWT($clientName, $data);
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
    private function getDataForClient(string $clientName, array $data): object
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
