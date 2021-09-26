<?php

namespace App\Controller;

use App\Entity\Celebrity;
use App\Entity\CelebrityLog;
use App\Entity\Company;
use App\Entity\CompanyLog;
use App\Entity\Representative;
use App\Entity\RepresentativeLog;
use App\Repository\CelebrityLogRepository;
use App\Repository\CompanyLogRepository;
use App\Repository\RepresentativeLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController
{

    /**
     * @Route("/data/log-celebrities", name="celebrities_log")
     */
    public function celebrities(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_TRAINER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(CelebrityLog::class);

        return $this->getLogsFromRepository($request->query->all(), $repository);
    }

    /**
     * @Route("/data/log-companies", name="companies_log")
     */
    public function companies(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_TRAINER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(CompanyLog::class);

        return $this->getLogsFromRepository($request->query->all(), $repository);
    }

    /**
     * @param $filter
     * @param CelebrityLogRepository|RepresentativeLogRepository|CompanyLogRepository $repository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function getLogsFromRepository($filter, $repository)
    {

        $entities = $repository->findLogs($filter);
        $total = $repository->findLogsCount($filter);

        $result = [];
        foreach ($entities as $entity) {
            /**
             * @var $entity CelebrityLog|RepresentativeLog
             */
            $new               = json_decode($entity->getNew(), true);
            $new['user']       = $entity->getUser()->getLogin();
            $new['valid_from'] = $entity->getDate()->format("Y-m-d H:i:s");

            $old               = json_decode($entity->getOld(), true);

            $result[] = ['new' => $new, 'old' => $old];
        }

        return $this->json(['data' => $result, 'total' => $total]);
    }
    /**
     * @Route("/data/log-representatives", name="representatives_log")
     */
    public function representatives(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_TRAINER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(RepresentativeLog::class);

        return $this->getLogsFromRepository($request->query->all(), $repository);
    }

    /**
     * @Route("/data/log-representatives-delete", name="representatives_log_delete", methods={"POST"})
     */
    public function representativesDelete(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        try {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                if (isset($data['id'])) {
                    $repository = $this->getDoctrine()->getRepository(Representative::class);

                    $representative = $repository->find($data['id']);

                    $manager = $this->getDoctrine()->getManager();

                    foreach ($representative->getEmails() as $email) {
                        $manager->remove($email);
                    }

                    foreach ($representative->getPhones() as $phone) {
                        $manager->remove($phone);
                    }
                    foreach ($representative->getRepresentativeConnections() as $connection) {
                        $manager->remove($connection);
                    }

                    $manager->remove($representative);
                    $manager->flush();
                }
            }

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/data/log-celebrities-delete", name="celebrities_log_delete", methods={"POST"})
     */
    public function celebritiesDelete(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        try {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                if (isset($data['id'])) {
                    $repository = $this->getDoctrine()->getRepository(Celebrity::class);

                    $celebrity = $repository->find($data['id']);

                    $manager = $this->getDoctrine()->getManager();

                    foreach ($celebrity->getRepresentativeConnections() as $connection) {
                        $manager->remove($connection);
                    }
                    foreach ($celebrity->getLinks() as $link) {
                        $manager->remove($link);
                    }

                    $manager->remove($celebrity);
                    $manager->flush();
                }
            }

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/data/log-companies-delete", name="celebrities_log_delete", methods={"POST"})
     */
    public function companiesDelete(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        try {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                if (isset($data['id'])) {
                    $repository = $this->getDoctrine()->getRepository(Company::class);

                    $company = $repository->find($data['id']);

                    $manager = $this->getDoctrine()->getManager();

                    foreach ($company->getLocations() as $location) {
                        $manager->remove($location);
                    }

                    $manager->remove($company);
                    $manager->flush();
                }
            }

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
