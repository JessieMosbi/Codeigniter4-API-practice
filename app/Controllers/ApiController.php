<?php

namespace App\Controllers;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;

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
}
