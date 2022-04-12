<?php
// This filter will allow the API check for the JWT before passing the request to the controller.
// If no JWT is provided or the provided JWT is expired, an HTTP_UNAUTHORIZED (401) response is returned by the API with an appropriate error message.
// Which route should be apply this filter, is defined in Config/filter.php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;

class JWTAuthentication implements FilterInterface
{
  // before sending to controller
  public function before(RequestInterface $request, $arguments = null): object
  {
    $authenticationHeader = $request->getServer('HTTP_AUTHORIZATION');

    try {
      helper('jwt'); // controller can also use this helper, don't need to redeclare

      $encodedToken = getSignedJWTFromRequest($authenticationHeader);
      if (!validateSignedJWT($encodedToken)) {
        return Services::response()
          ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
          ->setJSON(
            [
              'status' => 'fail',
              'message' => 'Authentication fail'
            ]
          );
      }

      return $request; // return request to controller
    }
    // JWT exception (include check fail) will be cached here
    catch (Exception $e) {
      return Services::response()
        ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
        ->setJSON(
          [
            'status' => 'fail',
            'message' => $e->getMessage()
          ]
        );
    }
  }

  // after sending to controller
  public function after(
    RequestInterface $request,
    ResponseInterface $response,
    $arguments = null
  ): void {
  }
}
