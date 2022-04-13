<?php

namespace App\Controllers;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services; // All of the core classes within CodeIgniter are provided as "services"
use CodeIgniter\Validation\Exceptions\ValidationException;

class ApiController extends BaseController
{
  // Parse raw JSON requests (content is stored in the body field of the request) sent to our API.
  public function getRequestInput(IncomingRequest $request): array
  {
    // convert to associative array
    $input = json_decode($request->getBody(), true);
    return $input;
  }

  // Used by our controllers to return JSON responses to the client.
  public function getResponse(array $responseBody, int $code = ResponseInterface::HTTP_OK): object
  {
    // By default, all response objects sent through CodeIgniter have HTTP caching turned off.
    return $this
      ->response
      ->setStatusCode($code)
      ->setJSON($responseBody);
  }

  // Validate the input parse by getRequestInput by rules that set in Controllers/.
  public function validateRequest(array $input, array $rules, array $messages = []): bool
  {
    $this->validator = Services::Validation()->setRules($rules);

    // If you replace the $rules array with the name of the group in config.
    // config file path: app/Config/Validation.php, namespace: Config\Validation
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
