<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;

/**
 * This filter will allow the API check for the JWT before passing the request to the controller.
 *
 * If no JWT is provided or the provided JWT is expired,
 * an HTTP_UNAUTHORIZED (401) response is returned by the API with an appropriate error message.
 *
 * Which route should be applied in this filter, was defined in Config/filter.php
 */
class JWTAuthentication implements FilterInterface
{
    /**
     * before sending to controller
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request
     * @param $arguments
     * @return object
     */
    public function before(RequestInterface $request, $arguments = null): object
    {
        try {
            helper('jwt'); // controller can also use this helper, don't need to redeclare

            $encodedToken = getSignedJWTFromRequest($request);
            if (!validateSignedJWT($encodedToken)) {
                return Services::response()
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'status' => 'fail',
                    'message' => 'Authentication fail'
                ]);
            }

            return $request; // return request to controller
        } catch (Exception $e) {
            // JWT exception (include check fail) will be cached here
            return Services::response()
            ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
            ->setJSON([
                'status' => 'fail',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * after sending to controller
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request
     * @param \CodeIgniter\HTTP\ResponseInterface $response
     * @param $arguments
     * @return void
     */
    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ): void {
    }
}
