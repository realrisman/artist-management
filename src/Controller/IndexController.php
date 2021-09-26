<?php

namespace App\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\HunterAPIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{

    protected $api;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->api = new HunterAPIService($doctrine);
    }

    /**
     * @Route("/", name="index")
     * @Route("/users")
     * @Route("/logs")
     * @Route("/import")
     * @Route("/celebrities")
     * @Route("/celebrities-need-verify")
     * @Route("/celebrities-unable-verify")
     * @Route("/representatives-need-verify")
     * @Route("/representatives-unable-verify")
     * @Route("/representatives")
     * @Route("/companies")
     * @Route("/company-merge")
     * @Route("/statistics")
     * @Route("/settings")
     * @Route("/emails")
     * @Route("/forbidden-representatives")
     * @Route("/unique-links")
     * @Route("/unique-links-companies")
     * @Route("/unique-links-celebrities")
     * @Route("/users/add")
     * @Route("/users/{id}")
     * @Route("/celebrities/{id}")
     * @Route("/companies/{id}")
     * @Route("/representatives/{id}")
     * @Route("/unique-link/{id}")
     * @Route("/unique-link-company/{id}")
     * @Route("/unique-link-celebrity/{id}")
     * @Route("/representatives/log/{id}")
     * @Route("/celebrities/log/{id}")
     * @Route("/companies/log/{id}")
     */
    public function index()
    {
        return new Response(file_get_contents(__DIR__ . "/../../public/index.html"));
    }


    /**
     * @param Request $request
     *
     * @Route("/data/check-email", name="check_email", methods={"POST"})
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkEmail(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        if (!$data)
            return $this->json(['success' => false], 500);
        $response = $this->api->callHunterApi($data['email']);
        if (!isset($response['data']))
            return $this->json(['success' => false, 'errors' => $response['errors']]);

        return $this->json(['success' => true, 'status' => $response['data']['status']]);
    }
}
