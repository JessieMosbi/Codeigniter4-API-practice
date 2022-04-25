<?php

namespace App\Controllers;

use App\Models\ClientModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * Class Client
 *
 * Provide Client CUD, include uploading pic file
 */
class Clients extends ApiController
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

    public function updateClient()
    {
        $rules = [
            'name'  => [
                'label' => 'name',
                'rules' => 'required|max_length[100]',
                'errors' => [
                    'required' => '{field} is required.'
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

            helper('JWT');
            $authenticationHeader = $this->request->getServer('HTTP_AUTHORIZATION');
            $encodedToken = getSignedJWTFromRequest($authenticationHeader);
            $clientId = getPayloadFromJWT($encodedToken, 'user')['id'];

            // upload new file
            if ($file = $this->request->getFile('avatar')) {
                $client = $this->clientModel->findClientById($clientId);
                $fileName = WRITEPATH . 'uploads/avatar/' . $client->avatar;

                if (is_file($fileName)) {
                    unlink($fileName);
                }

                if (!$file->isValid()) {
                    throw new Exception($file->getErrorString() . '(' . $file->getError() . ')');
                }
                $filePath = $file->store('/avatar/');
                $fileName = str_replace('/avatar/', '', $filePath);
            }

            $data = [
                'name' => $this->request->getVar('name'),
                'avatar' => (isset($fileName)) ? $fileName : null
            ];
            $this->clientModel->updateClientById($clientId, $data);

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
     * Delete tuple in client_basic Table
     *
     * Id is retrieved in token.
     *
     * @return object
     */
    public function deleteClient()
    {
        try {
            helper('JWT');
            $authenticationHeader = $this->request->getServer('HTTP_AUTHORIZATION');
            $encodedToken = getSignedJWTFromRequest($authenticationHeader);
            $clientId = getPayloadFromJWT($encodedToken, 'user')['id'];
            $this->clientModel->deleteClientById($clientId);

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
}
