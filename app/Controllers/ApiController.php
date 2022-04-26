<?php

/**
 * All of the core classes within CodeIgniter are provided as "services"
 */

namespace App\Controllers;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Config\Services;

/**
 * Class ApiController
 *
 * Provide basic function to deal with HTTP request.
 * ApiController will be extended by each api controller.
 */
class ApiController extends BaseController
{
    /**
     * Parse form or raw JSON requests sent to our API.
     *
     * JSON format content is stored in the body field of the request.
     * This function will convert content to associative array because validator can only receive array.
     *
     * @param IncomingRequest $request
     * @param string $type content-type in header
     * @return array
     */
    public function getRequestInput(IncomingRequest $request, string $type): array
    {
        if (strtoupper($type) === 'JSON') {
            $input = json_decode($request->getBody(), true);
        } elseif ($type === 'form') {
            $input = $request->getVar();
        }
        return $input;
    }

    /**
     * Used by our controllers to return plain JSON responses to the client.
     *
     * By default, all response objects sent through CodeIgniter have HTTP caching turned off.
     *
     * @param array $responseBody
     * @param int|null $code HTTP status code
     * @return object
     */
    public function getResponse(array $responseBody, int $code = ResponseInterface::HTTP_OK): object
    {
        return $this
        ->response
        ->setStatusCode($code)
        ->setJSON($responseBody);
    }

    /**
     * Used by our controllers to return encrypt JSON responses (Nested JWT) to the client.
     *
     * @param array $responseBody
     * @return object
     */
    public function getEncryptedResponse(array $responseBody): object
    {
        list($data, $result) = getEncryptJWT('test', $responseBody);
        return $this->getResponse(
            [
                'status' => 'success',
                'result' => $data,
                'decode' => $result
            ]
        );
    }

    /**
     * Validate the input parse by getRequestInput.
     *
     * Rules set in Controllers/ or config file: path: app/Config/Validation.php, namespace: Config\Validation.
     *
     * @param array $input
     * @param array $rules
     * @param array $messages error message
     * @return bool
     */
    public function validateRequest(array $input, array $rules, array $messages = []): bool
    {
        $this->validator = Services::Validation()->setRules($rules);

        // If you replace the $rules array with the name of the group in config.
        if (is_string($rules)) {
            $validation = config('Validation');

            if (!isset($validation->$rules)) {
                throw ValidationException::forRuleNotFound($rules);
            }

            // If no error message is defined, use the error message in the Config\Validation file
            if (!$messages) {
                $errorName = $rules . '_errors';
                $messages = $validation->$errorName ?? []; // TODO: ???
            }

            $rules = $validation->$rules;
        }

        return $this->validator->setRules($rules, $messages)->run($input);
    }
}
