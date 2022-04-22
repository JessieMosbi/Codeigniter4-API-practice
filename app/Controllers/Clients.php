<?php

namespace App\Controllers;

use App\Models\ClientModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * Class Member
 *
 * Provide Member CRUD, include uploading pic file
 */
class Clients extends ApiController
{
    public function __construct()
    {
        $this->clientModel = new ClientModel();
    }

    public function createClient()
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
            ]
        ];

        try {
            $input = $this->getRequestInput($this->request);
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
}
