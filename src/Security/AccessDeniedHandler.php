<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface, AuthenticationEntryPointInterface
{

    protected $json = [
        'message' => 'Please log in',
        'error'   => true
    ];
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {

        return new JsonResponse($this->json);
    }

    /*
    * This method receives the current Request object and the exception by which the exception
    * listener was triggered.
    *
    * The method should return a Response object
    */

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse($this->json);
    }
}
