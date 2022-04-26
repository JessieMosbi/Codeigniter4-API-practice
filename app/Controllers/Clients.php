<?php

namespace App\Controllers;

use App\Models\ClientModel;
use CodeIgniter\HTTP\IncomingRequest;
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
     * Update tuple in client_basic Table
     *
     * Params are 'multipart/form-data content' (form with file) type
     *
     * @return object
     */
    public function updateClient()
    {

        if (!$client = $this->getClient($this->request)) {
            return $this->getResponse(
                [
                    'status' => 'fail',
                    'message' => 'client is not exist',
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

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

            // upload new file
            if ($file = $this->request->getFile('avatar')) {
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
            $this->clientModel->updateClientById($client->id, $data);

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
        if (!$client = $this->getClient($this->request)) {
            return $this->getResponse(
                [
                    'status' => 'fail',
                    'message' => 'client is not exist',
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        try {
            $this->clientModel->deleteClientById($client->id);

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
     * Get client from database (by id in token)
     *
     * @param IncomingRequest $request
     * @return object
     */
    private function getClient(IncomingRequest $request): object
    {
        helper('JWT');
        $encodedToken = getSignedJWTFromRequest($request);
        $clientId = getPayloadFromJWT($encodedToken, 'user')['id'];
        return $this->clientModel->findClientById($clientId);
    }
}
