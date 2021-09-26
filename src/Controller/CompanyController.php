<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Celebrity;
use App\Entity\CelebrityLog;
use App\Entity\Company;
use App\Entity\CompanyLog;
use App\Entity\Location;
use App\Entity\RepresentativeConnection;
use App\Entity\Status;
use App\Service\WpAPIService;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CompanyController extends AbstractController
{

    /**
     * @Route("/data/company/{unid}", name="company_details")
     * @param $unid
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function details($unid)
    {

        $this->denyAccessUnlessGranted('ROLE_TRAINER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Company::class);
        $logRepository = $this->getDoctrine()->getRepository(CompanyLog::class);

        $company = $repository->find($unid);


        if (!is_null($company)) {
            $result = $company->jsonSerialize();
            foreach ($company->getRepresentativeConnections() as $representativeConnection) {
                $location = null;
                $result['celebrities'][] = [
                    'verifyRank'   => $representativeConnection->getNeedsVerifyFlag(),
                    'verifiedDate' => $representativeConnection->getLastVerified()->format("m/d/Y"),
                    'celebrity'    => $representativeConnection->getCelebrity()->getName(),
                    'created'      => is_null($representativeConnection->getCreated()) ? 'N/A' : $representativeConnection->getCreated()->format("n/j/y"),
                    'rc_id'        => $representativeConnection->getId(),

                ];
            }
            foreach ($company->getRepresentatives() as $representative) {
                $locationName = '';
                if ($representative->getLocation() instanceof Location) {
                    $locationName = $representative->getLocation()->getName();
                }
                $result['representatives'][] = [
                    'name' => $representative->getName(),
                    'id' => $representative->getUnid(),
                    'locationName' => $locationName
                ];
            }
            $sources = $logRepository->findSources($unid);

            foreach ($sources as $source) {
                $json = json_decode($source->getNew(), true);
                if (!empty($json['source'])) {
                    $result['sources'][] = [
                        'date'   => $source->getDate()->format('Y-m-d H:i:s'),
                        'source' => $json['source'],
                        'author' => $source->getUser()->getLogin(),
                    ];
                }
            }

            return $this->json($result);
        } else {
            return $this->json(['success' => false]);
        }
    }

    /**
     * @Route("/data/company", name="company", methods={"GET"})
     */
    public function index(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_WRITER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Company::class);

        /** @var Company[] $companies */
        $companies = $repository->findByFilter($request->query->all());

        $result = [];
        foreach ($companies as $company) {
            $result[] = [
                'id'               => $company->getId(),
                'name'             => $company->getName(),
                'status'           => $company->getStatus(),
                'wp_id'            => $company->getWpId(),
                'type'             => 'company',
                'need_verify_flag' => $company->getNeedsVerifyFlag()
            ];
        }

        return $this->json(['data' => $result, 'total' => count($companies)]);
    }

    /**
     * @Route("/data/company", name="company_save", methods={"POST"})
     */
    public function save(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        error_log("Controller: starting saving company");
        $this->denyAccessUnlessGranted('ROLE_WRITER', null, 'Unable to access this page!');

        $data = [];
        try {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
            }
            $repository = $this->getDoctrine()->getRepository(Company::class);
            $crepository = $this->getDoctrine()->getRepository(Category::class);
            $lrepository = $this->getDoctrine()->getRepository(Location::class);
            $manager = $this->getDoctrine()->getManager();


            if (!empty($data['id'])) {
                $company = $repository->find($data['id']);
            } else {
                $search['name'] = $data['name'];
                $company = $repository->findOneBy($search);
                if (is_null($company)) {
                    $company = new Company();
                    $company->setCreated(new \DateTime());
                    $company->setWpId(0);
                    $company->setUser($this->getUser());
                }
            }

            $log = new CompanyLog();

            $log->setUser($this->getUser());
            $log->setDate(new \DateTime());
            $log->setOld(json_encode($company->logSerialize()));

            if ($authChecker->isGranted('ROLE_TRAINER')) {

                if (isset($data['status'])) {
                    if ($data['status'] != Status::LIVE) {
                        $company->setStatus($data['status']);
                    }
                    if ($authChecker->isGranted('ROLE_EDITOR')) {
                        $company->setStatus($data['status']);
                    }
                }
                if (isset($data['name'])) {
                    $company->setName($data['name']);
                }
                if (isset($data['description'])) {
                    $company->setDescription($data['description']);
                }
                if (isset($data['website'])) {
                    $company->setWebsite($data['website']);
                }
                if (isset($data['instagram'])) {
                    $company->setInstagram($data['instagram']);
                }

                if (isset($data['image'])) {
                    $company->setImage($data['image']);
                }
                if (isset($data['primaryCategory']) && isset($data['primaryCategory']['id'])) {
                    $category = $crepository->find($data['primaryCategory']['id']);
                    $company->setPrimaryCategory($category);
                }
                if (isset($data['source'])) {
                    $company->setSource($data['source']);
                } else {
                    $company->setSource('');
                }

                $company->setUser($this->getUser());
                $company->setLastUpdatedAt(new \DateTime());

                $processedLocationIds = [];
                if (isset($data['locations'])) {
                    if (is_array($data['locations'])) {
                        foreach ($data['locations'] as $l) {
                            if (!empty($l['id'])) {
                                $location = $lrepository->find($l['id']);
                            } else {
                                $location = new Location();
                            }
                            $location->setName($l['name']);
                            $location->setEmail($l['email']);
                            $location->setPhone($l['phone']);
                            $location->setPostalAddress($l['postal_address']);
                            $location->setVisitorAddress($l['visitor_address']);
                            $manager->persist($location);

                            $company->addLocation($location);
                            $processedLocationIds[] = $location->getId();
                        }
                    }
                }
                //iterate over company locations and remove those not received from server
                foreach ($company->getLocations() as $location) {
                    if (!is_null($location->getId()) && !in_array($location->getId(), $processedLocationIds)) {
                        $company->removeLocation($location);
                        foreach ($location->getRepresentatives() as $representative) {
                            $location->removeRepresentative($representative);
                        }
                        $manager->remove($location);
                    }
                }

                $categoryIds = [];
                if (isset($data['categories'])) {
                    if (is_array($data['categories'])) {
                        $categoryIds = array_map(function ($cat) {
                            return $cat['id'];
                        }, $data['categories']);

                        foreach ($data['categories'] as $c) {
                            if (isset($c['id']) && in_array($c['id'], $categoryIds)) {
                                $category = $crepository->find($c['id']);
                                $company->addCategory($category);
                            }
                        }
                    }
                }
                $cats = $company->getCategories();
                foreach ($cats as $cat) {
                    if (!in_array($cat->getId(), $categoryIds)) {
                        $company->removeCategory($cat);
                    }
                }
            }

            if ($this->get('security.authorization_checker')->isGranted('ROLE_TRAINER')) {
                if (true === $data['verify']) {
                    $company->setNeedsVerifyFlag(0);
                    $company->setLastVerified(new \DateTime());
                    $company->addVerificationLog($this->getUser()->getLogin());
                }
            }

            $filename = str_replace(" ", "-", $company->getName()) . "-Contact-Information";
            $filename = str_replace('"', '', $filename);
            $filename = str_replace("'", "", $filename);
            if ($company->getStatus() == Status::LIVE) {
                if ("" == $company->getImage()) {
                    $avatar = new UploadedFile(__DIR__ . "/../../public/assets/default_company_picture.jpg", $filename . ".jpg");
                    if (!isset($api)) {
                        $api = new WpAPIService($this->getDoctrine());
                    }
                    $response = $api->uploadFile($avatar);

                    $company->setImage($response['source_url']);
                    $data['attachment_id'] = $response['id'];
                }
            }
            if ("" == $company->getImageAlt()) {
                $company->setImageAlt($filename);
            }
            if ("" == $company->getImageTitle()) {
                $company->setImageTitle(str_replace("-", " ", $filename));
            }

            $manager->persist($company);

            if ($company->getStatus() != Status::QA && $company->getStatus() != Status::READY) {
                $api = new WpAPIService($this->getDoctrine());
                $wp_id = $api->saveCompany($company);
                $company->setWpId($wp_id);
                $manager->persist($company);
                if (!empty($company->getImage()) && empty($data['attachment_id'])) {
                    $data['attachment_id'] = $api->getAttachmentIdByUrl($company->getImage());
                }
                if (isset($data['attachment_id']) && false !== $data['attachment_id']) {
                    $api->setFeaturedImage($company, $data['attachment_id']);
                }
            }


            if (isset($log) && !is_null($company->getId())) {
                $log->setUnid($company->getId());
                $log->setNew(json_encode($company->logSerialize()));
                $manager->persist($log);
            }

            $manager->flush();
            $data = ['success' => true];
        } catch (\Exception $e) {
            $data = ['success' => false, 'error' => $e->getMessage()];
            if ($e instanceof ClientException) {
                $data['request'] = $e->getRequest()->getBody()->getContents();
                $data['response'] = $e->getResponse()->getBody()->getContents();
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/data/company-import", name="company_import",methods={"POST"})
     */
    public function import(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        try {
            $response = ['success' => true];
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                if (!empty($data['id'])) {
                    $repository = $this->getDoctrine()->getRepository(Company::class);
                    $company = $repository->find($data['id']);
                    $api = new WpAPIService($this->getDoctrine());
                    $company = $api->importCompany($company);
                    $manager = $this->getDoctrine()->getManager();
                    if ($company instanceof Company) {
                        $company->setUser($this->getUser());
                        $company->setLastUpdatedAt(new \DateTime());
                        $manager->persist($company);
                        $manager->flush();
                    }
                }
            }
        } catch (\Exception $e) {

            $response = [
                'success' => false,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ];
        }

        return $this->json($response);
    }

    /**
     * Updates celebrity needs update flag
     * @Route("/data/company-verify/{id}", name="company_verify", methods={"POST"})
     */
    public function verify($id, Request $request, Connection $connection)
    {

        $this->denyAccessUnlessGranted('ROLE_WRITER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Company::class);
        $company = $repository->find($id);
        $company->addVerificationLog($this->getUser()->getLogin());
        $logs = $company->getVerificationLog();

        $today = new \DateTime();
        $connection->update('company', [
            'last_verified'     => $today->format("Y-m-d"),
            'needs_verify_flag' => 0,
            'verification_log'  => $logs
        ], [
            'id' => $id
        ]);

        $data = ['success' => true];

        return $this->json($data);
    }

    /**
     * @Route("/data/company-log/{unid}", name="company_log")
     */
    public function log($unid, Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_TRAINER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(CompanyLog::class);

        $logs = $repository->findBy(['unid' => $unid], ['date' => 'DESC']);

        $result = [];
        foreach ($logs as $celebrityLog) {
            /**
             * @var $celebrityLog CompanyLog
             */
            $data['new'] = json_decode($celebrityLog->getNew(), true);
            $data['old'] = json_decode($celebrityLog->getOld(), true);
            $data['new']['user'] = $celebrityLog->getUser()->getLogin();
            $data['new']['valid_from'] = $celebrityLog->getDate()->format("Y-m-d H:i:s");
            $result[] = $data;
        }

        return $this->json($result);
    }


    /**
     * Updates celebrity needs update flag
     * @Route("/data/company-connection-delete/{id}", name="company_connection_delete", methods={"POST"})
     */
    public function representativeConnectionDelete($id, Request $request, Connection $connection)
    {

        $this->denyAccessUnlessGranted('ROLE_WRITER', null, 'Unable to access this page!');

        $rcrepository = $this->getDoctrine()->getRepository(RepresentativeConnection::class);

        $rc = $rcrepository->findOneBy(['id' => $id]);
        if ($rc && $rc->isCompany()) {
            $company = $rc->getCompany();
            $companyLog = new CompanyLog();
            $companyLog->setUser($this->getUser());
            $companyLog->setUnid($company->getUnid());
            $companyLog->setDate(new \DateTime());
            $companyLog->setOld(json_encode($company->logSerialize()));

            $celebrity = $rc->getCelebrity();
            $log = new CelebrityLog();
            $log->setUser($this->getUser());
            $log->setUnid($celebrity->getUnid());
            $log->setDate(new \DateTime());
            $log->setOld(json_encode($celebrity->logSerialize()));

            $connection->delete(
                'representative_connection',
                [
                    'id' => $id,
                ]
            );
            $manager = $this->getDoctrine()->getManager();
            $manager->refresh($rc->getCompany());
            $manager->refresh($rc->getCelebrity());

            $companyLog->setNew(json_encode($rc->getCompany()->logSerialize()));
            $log->setNew(json_encode($rc->getCelebrity()->logSerialize()));

            $manager->persist($companyLog);
            $manager->persist($log);
            $manager->flush();
            $api = new WpAPIService($this->getDoctrine());
            $api->saveCelebrity($rc->getCelebrity());
        }

        $data = ['success' => true];

        return $this->json($data);
    }


    /**
     * Merges company
     * @Route("/data/company-merge", name="company_merge", methods={"POST"})
     */
    public function merge(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_WRITER', null, 'Unable to access this page!');

        $manager = $this->getDoctrine()->getManager();
        $comRepository = $this->getDoctrine()->getRepository(Company::class);
        try {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
            }

            if (!empty($data['source']) && !empty($data['target'])) {
                $target = $comRepository->find($data['target']);
                if (!is_null($target) && is_array($data['source'])) {
                    $api = new WpAPIService($this->getDoctrine());
                    foreach ($data['source'] as $id) {
                        $source = $comRepository->find($id);
                        if (!is_null($source)) {
                            foreach ($source->getRepresentativeConnections() as $representativeConnection) {
                                $source->removeRepresentativeConnection($representativeConnection);
                                $target->addRepresentativeConnection($representativeConnection);
                                $manager->persist($representativeConnection);
                            }
                            foreach ($source->getRepresentatives() as $representative) {
                                $representative->removeCompany($source);
                                $representative->addCompany($target);
                                $source->removeRepresentative($representative);
                                $manager->persist($representative);
                                $api->saveRepresentative($representative);
                            }
                            foreach ($source->getLocations() as $location) {
                                foreach ($location->getRepresentatives() as $representative) {
                                    $location->removeRepresentative($representative);
                                }
                                $source->removeLocation($location);
                                $manager->remove($location);
                            }
                        }
                        $manager->remove($source);
                        $api->deleteCompany($source);
                    }
                    $api->saveCompany($target);
                    $manager->persist($target);
                    $manager->flush();
                }
            }
            $response = ['success' => true];
        } catch (\Exception $e) {

            $response = [
                'success' => false,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ];
        }

        return $this->json($response);
    }
}
