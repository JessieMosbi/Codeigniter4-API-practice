<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\MemberModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * Member Client
 *
 * Provide Member Read function
 */
class Members extends ApiController
{
    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->memberModel = new MemberModel();
    }

    public function getMembers()
    {
        $rules = [
            'school'  => [
                'label' => 'school',
                'rules' => 'permit_empty|exact_length[6]'
            ],
            'status'  => [
                'label' => 'status',
                'rules' => 'permit_empty|is_natural_no_zero|validateStatusValue[status]',
                'errors' => [
                    'validateStatusValue' => '{field} is not a valid value'
                ]
            ]
        ];

        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validateRequest($input, $rules)) {
                return $this->getResponse(
                    [
                        'status' => 'fail',
                        'message' => $this->validator->getErrors(),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST
                );
            }

            $data = [];
            if ($this->request->getVar('school')) {
                $data['school_id'] = $this->request->getVar('school');
            }
            if ($this->request->getVar('status')) {
                $data['status_id'] = $this->request->getVar('status');
            }
            $members = $this->memberModel->findMembersBySchoolAndStatus($data);

            return $this->getResponse(
                [
                    'status' => 'success',
                    'result' => $members
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
