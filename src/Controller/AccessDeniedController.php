<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AccessDeniedController extends AbstractController
{

    /**
     * @Route("/403", name="access_denied")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Please log in',
            'error'   => true,
        ], 401);
    }
}
