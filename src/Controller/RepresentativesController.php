<?php

namespace App\Controller;


use App\Entity\Category;
use App\Entity\Celebrity;
use App\Entity\CelebrityLog;
use App\Entity\Company;
use App\Entity\ContactChange;
use App\Entity\Email;
use App\Entity\ForbiddenRepresentative;
use App\Entity\Location;
use App\Entity\Phone;
use App\Entity\Representative;
use App\Entity\RepresentativeConnection;
use App\Entity\RepresentativeLog;
use App\Entity\RepresentativeType;
use App\Entity\Status;
use App\Service\WpAPIService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RepresentativesController extends AbstractController
{

    /**
     * @Route("/data/agents", name="agents")
     */
    public function agents(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');
        $repository = $this->getDoctrine()->getRepository(RepresentativeConnection::class);

        return $this->renderRepresentativeConnectionsJson($repository->findAgentsByName($request->query->get('name', false)));
    }

    /**
     * @Route("/data/managers", name="managers")
     */
    public function managers(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(RepresentativeConnection::class);

        return $this->renderRepresentativeConnectionsJson($repository->findManagersByName($request->query->get('name', false)));
    }

    /**
     * @Route("/data/publicists", name="publicists")
     */
    public function publicists(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(RepresentativeConnection::class);

        return $this->renderRepresentativeConnectionsJson($repository->findPublicistsByName($request->query->get('name', false)));
    }

    /**
     * @Route("/data/representatives", name="representatives")
     */
    public function representatives(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Representative::class);

        return $this->renderRepresentativeJson(
            $repository->findRepresentativesByName($request->query->get('name', false))
        );
    }

    /**
     * @Route("/data/representative/{unid}", name="representative_details")
     */
    public function details($unid, Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_TRAINER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Representative::class);
        $logRepository = $this->getDoctrine()->getRepository(RepresentativeLog::class);
        $representative = $repository->findOneByUnid($unid);

        if (!is_null($representative)) {
            $result = $representative->jsonSerialize();
            $sources = $logRepository->findSources($representative->getUnid());

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
            $today = new \DateTime();
            foreach ($representative->getRepresentativeConnections() as $representativeConnection) {
                if ($representativeConnection->getCelebrity()->getValidTill() > $today)
                    $result['celebrities'][] = [
                        'verifyRank'   => $representativeConnection->getNeedsVerifyFlag(),
                        'verifiedDate' => $representativeConnection->getLastVerified()->format("m/d/Y"),
                        'celebrity'    => $representativeConnection->getCelebrity()->getName(),
                        'created'      => is_null($representativeConnection->getCreated()) ? 'N/A' : $representativeConnection->getCreated()->format("n/j/y"),
                        'rc_id'        => $representativeConnection->getId(),
                    ];
            }

            return $this->json($result);
        } else {
            return $this->json(['success' => false]);
        }
    }

    /**
     * @Route("/data/representative", name="representative_list", methods={"GET"})
     */
    public function representativeList(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Representative::class);
        $representatives = $repository->findRepresentativesByFilter($request->query->all());
        $result = [];
        foreach ($representatives as $rep) {
            $result[] = $rep->jsonSerialize();
        }

        return $this->json(['data' => $result, 'total' => count($representatives)]);
    }

    /**
     * @Route("/data/representative", name="representative_save")
     */
    public function save(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->denyAccessUnlessGranted('ROLE_TRAINER', null, 'Unable to access this page!');

        $data = [];
        try {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
            }

            $repository = $this->getDoctrine()->getRepository(Representative::class);
            $crepository = $this->getDoctrine()->getRepository(Category::class);
            $companyRepository = $this->getDoctrine()->getRepository(Company::class);
            $trepository = $this->getDoctrine()->getRepository(RepresentativeType::class);
            $prepository = $this->getDoctrine()->getRepository(Phone::class);
            $erepository = $this->getDoctrine()->getRepository(Email::class);
            $rcrepository = $this->getDoctrine()->getRepository(RepresentativeConnection::class);
            $lrepository = $this->getDoctrine()->getRepository(Location::class);
            $celebRepository = $this->getDoctrine()->getRepository(Celebrity::class);
            $manager = $this->getDoctrine()->getManager();

            if (!empty($data['id'])) {
                $rep = $repository->findOneByUnid($data['id']);
            } else {
                //check if celebrity is allowed to be added
                $blocked = $repository->findOneBy(['name' => $data['name'], 'status' => Status::DELETED]);
                if (!is_null($blocked)) {
                    return $this->json(['success' => false, 'error' => 'Representative is on forbidden list: ' . $blocked->getRemoveReason(), 'blocked' => true]);
                }

                $search['name'] = $data['name'];
                if (isset($data['company'])) {
                    $search['company'] = $data['company'];
                }
                $rep = $repository->findOneBy($search);
                if (is_null($rep)) {
                    $rep = new Representative();
                    $rep->setUser($this->getUser());
                }
            }

            $log = new RepresentativeLog();

            $log->setUser($this->getUser());
            $log->setDate(new \DateTime());
            $log->setOld(json_encode($rep->logSerialize()));

            if ($data['spot_checked'] != $rep->getSpotChecked())
                $log->setSpotChecked(true);

            $rep->setValidFrom(new \DateTime());
            $rep->setUser($this->getUser());

            if (isset($data['status'])) {
                if ($data['status'] != Status::LIVE) {
                    $rep->setStatus($data['status']);
                }
                if ($authChecker->isGranted('ROLE_EDITOR')) {
                    $rep->setStatus($data['status']);
                }
            }

            if (isset($data['name'])) {
                $rep->setName($data['name']);
            }

            if (isset($data['source'])) {
                $rep->setSource($data['source']);
            } else {
                $rep->setSource('');
            }
            if (isset($data['visitor_address'])) {
                $rep->setVisitorAddress($data['visitor_address']);
            }
            if (isset($data['mailing_address'])) {
                $rep->setMailingAddress($data['mailing_address']);
            }
            if (!empty($data['location'])) {
                $location = $lrepository->find($data['location']['id']);
                $rep->setLocation($location);
            } else {
                $rep->setLocation(null);
            }
            if (isset($data['image'])) {
                $rep->setImage($data['image']);
            }
            if (isset($data['instagram'])) {
                $rep->setInstagram($data['instagram']);
            }
            if (isset($data['image_alt'])) {
                $rep->setImageAlt($data['image_alt']);
            }
            if (isset($data['image_title'])) {
                $rep->setImageTitle($data['image_title']);
            }
            if (isset($data['remove_reason'])) {
                $rep->setRemoveReason($data['remove_reason']);
            }
            if (isset($data['allows_to_add_phone'])) {
                $rep->setAllowsToAddPhone($data['allows_to_add_phone']);
            }
            if (isset($data['unable_to_verify'])) {
                $rep->setUnableToVerify($data['unable_to_verify']);
            }
            if (isset($data['spot_checked'])) {
                $rep->setSpotChecked($data['spot_checked']);
            }

            if (isset($data['primaryCategory']) && isset($data['primaryCategory']['id'])) {
                $category = $crepository->find($data['primaryCategory']['id']);
                $rep->setPrimaryCategory($category);
            }

            if (isset($data['type'])) {
                $types = $rep->getType();
                foreach ($types as $type) {
                    $rep->removeType($type);
                }
                $type = $trepository->findOneBy(['name' => $data['type']]);
                if (!$type) {
                    $type = new RepresentativeType($data['type']);
                    $manager->persist($type);
                }
                $rep->addType($type);
            }
            $processedPhones = [];
            $editedContacts = 0;
            if (isset($data['phones'])) {
                if (is_array($data['phones'])) {
                    foreach ($data['phones'] as $number) {
                        $phone = $prepository->findOneBy(['phone' => $number, 'agent' => $rep, 'deleted' => 0]);
                        if (!$phone) {
                            $phone = new Phone();
                            $phone->setAgent($rep);
                            $rep->addPhone($phone);
                            $editedContacts++;
                        }
                        $phone->setPhone($number);
                        $manager->persist($phone);
                        $processedPhones[] = $number;
                    }
                }
            }
            foreach ($rep->getPhones() as $phone) {
                if (!in_array($phone->getPhone(), $processedPhones)) {
                    $rep->removePhone($phone);
                    $manager->remove($phone);
                    $editedContacts++;
                }
            }

            $processedEmails = [];
            if (isset($data['emails'])) {
                if (is_array($data['emails'])) {
                    foreach ($data['emails'] as $address) {
                        $email = $erepository->findOneBy(['email' => $address, 'agent' => $rep, 'deleted' => 0]);
                        if (!$email) {
                            $email = new Email();
                            $email->setAgent($rep);
                            $rep->addEmail($email);
                            $editedContacts++;
                        }
                        $email->setEmail($address);
                        $manager->persist($email);
                        $processedEmails[] = $address;
                    }
                }
            }
            foreach ($rep->getEmails() as $email) {
                if (!in_array($email->getEmail(), $processedEmails)) {
                    $rep->removeEmail($email);
                    $manager->remove($email);
                    $editedContacts++;
                }
            }

            /**
             * add/remove companies
             */
            $processedCompanies = [];
            if (isset($data['companies'])) {
                if (is_array($data['companies'])) {
                    foreach ($data['companies'] as $company) {
                        if (isset($company['id'])) {
                            $company = $companyRepository->find($company['id']);
                        } else {
                            $company = $this->createCompany($data, $company['name']);
                            $rep->setLocation($company->getLocations()->first());
                        }
                        $rep->addCompany($company);
                        $processedCompanies[] = $company->getId();
                    }
                }
            }
            foreach ($rep->getCompanies() as $company) {
                if ($company->getId() && !in_array($company->getId(), $processedCompanies)) {
                    $rep->removeCompany($company);
                }
            }

            $rep->setCompanyName('');
            if ($firstCompany = $rep->getCompanies()->first()) {
                if ($firstCompany instanceof Company) {
                    $rep->setCompanyName($firstCompany->getName());
                }
            }


            $categoryIds = [];
            if (isset($data['categories'])) {
                if (is_array($data['categories'])) {
                    $categoryIds = array_map(
                        function ($cat) {

                            return $cat['id'];
                        },
                        $data['categories']
                    );
                    foreach ($data['categories'] as $c) {
                        if (isset($c['id'])) {
                            $category = $crepository->find($c['id']);
                            $rep->addCategory($category);
                        }
                    }
                }
            }
            $cats = $rep->getCategories();
            foreach ($cats as $cat) {
                if (!in_array($cat->getId(), $categoryIds)) {
                    $rep->removeCategory($cat);
                }
            }
            $hasNewCelebrities = false;
            if (isset($data['celebrities']) && is_array($data['celebrities'])) {
                foreach ($data['celebrities'] as $celebrity) {
                    if (isset($celebrity['verify']) && true === $celebrity['verify']) {
                        $rc = $rcrepository->find($celebrity['rc_id']);
                        $rc->setLastVerified(new \DateTime());
                        $rc->setNeedsVerifyFlag(0);
                        $rc->addVerificationLog($this->getUser()->getLogin());
                        $manager->persist($rc);
                    }
                    if (is_null($celebrity['rc_id']) && !empty($celebrity['celebrity_id'])) {
                        $celebrityEntity = $celebRepository->findOneByUnid($celebrity['celebrity_id']);
                        if (!is_null($celebrityEntity)) {
                            $celebLog = new CelebrityLog();
                            $celebLog->setUser($this->getUser());
                            $celebLog->setUnid($celebrityEntity->getUnid());
                            $celebLog->setDate(new \DateTime());
                            $celebLog->setOld(json_encode($celebrityEntity->logSerialize()));

                            $rc = new RepresentativeConnection();
                            $celebrityEntity->addRepresentativeConnection($rc);
                            $rc->setRepresentative($rep);
                            $rc->setCelebrity($celebrityEntity);
                            $rc->setType($celebrity['type']);
                            $rc->setTerritory($celebrity['territory']);
                            $rc->setPosition(0);
                            $rc->setLastVerified(new \DateTime());
                            $rc->setNeedsVerifyFlag(0);
                            $rep->addRepresentativeConnection($rc);

                            $celebLog->setNew(json_encode($celebrityEntity->logSerialize()));
                            $manager->persist($celebLog);
                            $manager->persist($rc);

                            $hasNewCelebrities = true;
                        }
                    }
                }
            }

            if (true === $data['verify']) {
                $rep->setNeedsVerifyFlag(0);
                $rep->setLastVerified(new \DateTime());
                $rep->addVerificationLog($this->getUser()->getLogin());
                $rep->setUnableToVerify(false);
            }

            $filename = str_replace(" ", "-", $rep->getName()) . "-Contact-Information";
            $filename = str_replace('"', '', $filename);
            $filename = str_replace("'", "", $filename);

            if ($rep->getStatus() == Status::LIVE) {
                if ("" == $rep->getImage()) {
                    $avatar = new UploadedFile(__DIR__ . "/../../public/assets/default_representative_picture.jpg", $filename . ".jpg");
                    if (!isset($api)) {
                        $api = new WpAPIService($this->getDoctrine());
                    }
                    $response = $api->uploadFile($avatar);

                    $rep->setImage($response['source_url']);
                    $data['attachment_id'] = $response['id'];
                }
            }

            if ("" == $rep->getImageAlt()) {
                $rep->setImageAlt($filename);
            }
            if ("" == $rep->getImageTitle()) {
                $rep->setImageTitle(str_replace("-", " ", $filename));
            }

            $manager->persist($rep);

            if ($rep->getStatus() != Status::QA && $rep->getStatus() != Status::READY) {
                $api = new WpAPIService($this->getDoctrine());
                if (isset($company)) {
                    $wp_id = $api->saveCompany($company);
                    $company->setWpId($wp_id);
                    $manager->persist($company);
                }
                $wp_id = $api->saveRepresentative($rep);
                $rep->setWpId($wp_id);
                $manager->persist($rep);

                if (!empty($rep->getImage()) && empty($data['attachment_id'])) {
                    $data['attachment_id'] = $api->getAttachmentIdByUrl($rep->getImage());
                }
                if (isset($data['attachment_id']) && false !== $data['attachment_id']) {
                    $api->setFeaturedImage($rep, $data['attachment_id']);
                }

                foreach ($rep->getRepresentativeConnections() as $connection) {
                    $newConnectedCelebrities[] = $connection->getCelebrity()->getId();
                    $api->saveCelebrity($connection->getCelebrity());
                }
            }

            if (isset($log)) {
                $log->setUnid($rep->getUnid());
                $log->setNew(json_encode($rep->logSerialize()));
                $manager->persist($log);
            }

            if (0 != $editedContacts && $rep->getStatus() == Status::LIVE) {
                $cc = new ContactChange();
                $cc->setUnid($rep->getUnid());
                $cc->setCelebrities(0);
                $cc->setAuthor($this->getUser());
                $manager->persist($cc);
            }

            $manager->flush();

            $data = ['success' => true];
        } catch (\Exception $e) {

            $data = ['success' => false, 'error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()];
        }

        return $this->json($data);
    }

    /**
     * Updates representative needs update flag
     * @Route("/data/representative-verify/{id}", name="representative_verify", methods={"POST"})
     */
    public function representativeVerify($id, Connection $connection)
    {

        $this->denyAccessUnlessGranted('ROLE_WRITER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Representative::class);
        //use real entity to get updated verification log
        $rep = $repository->findOneByUnid($id);
        $rep->addVerificationLog($this->getUser()->getLogin());
        $logs = $rep->getVerificationLog();

        $today = new \DateTime();
        $connection->update(
            'representative',
            [
                'last_verified'     => $today->format("Y-m-d"),
                'needs_verify_flag' => 0,
                'verification_log'  => $logs
            ],
            [
                'unid' => $id,
            ]
        );

        $data = ['success' => true];

        return $this->json($data);
    }

    /**
     * Updates representative connection needs update flag
     * @Route("/data/representative-connection-verify/{id}", name="representative_connection_verify", methods={"POST"})
     */
    public function representativeConnectionVerify($id, Request $request, Connection $connection)
    {

        $this->denyAccessUnlessGranted('ROLE_WRITER', null, 'Unable to access this page!');
        $rcrepository = $this->getDoctrine()->getRepository(RepresentativeConnection::class);
        //use real entity to get updated verification log
        $rc = $rcrepository->findOneBy(['id' => $id]);
        $rc->addVerificationLog($this->getUser()->getLogin());
        $logs = $rc->getVerificationLog();

        $today = new \DateTime();
        $connection->update(
            'representative_connection',
            [
                'last_verified'     => $today->format("Y-m-d"),
                'needs_verify_flag' => 0,
                'verification_log'  => $logs
            ],
            [
                'id' => $id,
            ]
        );

        $data = ['success' => true];

        return $this->json($data);
    }

    /**
     * Updates celebrity needs update flag
     * @Route("/data/representative-connection-delete/{id}", name="representative_connection_delete", methods={"POST"})
     */
    public function representativeConnectionDelete($id, Connection $connection)
    {

        $this->denyAccessUnlessGranted('ROLE_WRITER', null, 'Unable to access this page!');

        $rcrepository = $this->getDoctrine()->getRepository(RepresentativeConnection::class);
        $crepository = $this->getDoctrine()->getRepository(Celebrity::class);
        $rrepository = $this->getDoctrine()->getRepository(Representative::class);

        $rc = $rcrepository->findOneBy(['id' => $id]);
        if ($rc) {
            $rep = $rc->getRepresentative();
            $rep->setSource('');
            $repLog = new RepresentativeLog();
            $repLog->setUser($this->getUser());
            $repLog->setUnid($rep->getUnid());
            $repLog->setDate(new \DateTime());
            $repLog->setOld(json_encode($rep->logSerialize()));

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
            $manager->refresh($rc->getRepresentative());
            $manager->refresh($rc->getCelebrity());

            $rc->getRepresentative()->setSource('');
            $repLog->setNew(json_encode($rc->getRepresentative()->logSerialize()));
            $log->setNew(json_encode($rc->getCelebrity()->logSerialize()));

            $manager->merge($repLog);
            $manager->merge($log);
            $manager->flush();
            $api = new WpAPIService($this->getDoctrine());
            $api->saveCelebrity($rc->getCelebrity());
        }

        $data = ['success' => true];

        return $this->json($data);
    }

    /**
     * @Route("/data/companies", name="companies")
     */
    public function companies(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Company::class);

        $companies = $repository->findCompaniesByName($request->query->get('name', false));
        $json = [];
        foreach ($companies as $company) {
            $json[] = [
                'id'      => $company->getId(),
                'name'    => $company->getName(),
                'company' => $company->getName(),
                'type'    => 'company'
            ];
        }
        return $this->json($json);
    }

    /**
     * @Route("/data/representative-import", name="representative_import",methods={"POST"})
     */
    public function import(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        try {
            $response = ['success' => true];
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                if (!empty($data['id'])) {
                    $repository = $this->getDoctrine()->getRepository(Representative::class);
                    $rep = $repository->findOneByUnid($data['id']);
                    $api = new WpAPIService($this->getDoctrine());
                    $rep = $api->importRepresentative($rep);
                    $manager = $this->getDoctrine()->getManager();
                    if ($rep instanceof Representative) {
                        if ($rep->getValidTill() != new \DateTime('2099-12-31')) {
                            $this->removeRepresentative($rep);
                            $response = ['success' => true, 'removed' => true];
                        } else {
                            $rep->setUser($this->getUser());
                            $manager->persist($rep);
                            $manager->flush();
                        }
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


    protected function renderRepresentativeConnectionsJson($representativeconnections)
    {

        $result = [];
        /**
         * @var $representativeconnections RepresentativeConnection[]
         */
        foreach ($representativeconnections as $representativeConnection) {
            $result[] = [
                'id'      => $representativeConnection->getRepresentative()->getId(),
                'name'    => $representativeConnection->getConnectedName(),
                'company' => $representativeConnection->getConnectedCompanyName(),
                'type'    => $representativeConnection->isCompany() ? 'company' : 'rep'
            ];
        }

        return $this->json($result);
    }

    protected function renderRepresentativeJson($representatives)
    {

        $result = [];
        /**
         * @var $representatives Representative[]
         */
        foreach ($representatives as $publicist) {
            $result[] = [
                'id'      => $publicist->getUnid(),
                'name'    => $publicist->getName(),
                'company' => $publicist->getCompanyName(),
                'type'    => 'rep'
            ];
        }

        return $this->json($result);
    }

    /**
     * @Route("/data/representative-log/{unid}", name="representative_log")
     */
    public function log($unid)
    {

        $this->denyAccessUnlessGranted('ROLE_TRAINER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(RepresentativeLog::class);

        $logs = $repository->findBy(['unid' => $unid], ['date' => 'DESC']);

        $result = [];
        foreach ($logs as $representativeLog) {
            /**
             * @var $representativeLog RepresentativeLog
             */
            $data['new'] = json_decode($representativeLog->getNew(), true);
            $data['old'] = json_decode($representativeLog->getOld(), true);
            $data['new']['user'] = $representativeLog->getUser()->getLogin();
            $data['new']['valid_from'] = $representativeLog->getDate()->format("Y-m-d H:i:s");
            $result[] = $data;
        }

        return $this->json($result);
    }


    protected function removeRepresentative(Representative $representative)
    {

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

    /**
     * @Route("/data/representative-delete", name="representative_delete", methods={"POST"})
     */
    public function representativesDelete(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        try {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                if (isset($data['id'])) {
                    $repository = $this->getDoctrine()->getRepository(Representative::class);

                    $representative = $repository->findOneByUnid($data['id']);

                    $this->removeRepresentative($representative);
                    $api = new WpAPIService($this->getDoctrine());
                    $api->deleteRepresentative($representative);
                }
            }

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/data/representative-delete-block", name="representative_delete_block", methods={"POST"})
     */
    public function representativesDeleteAndBlock(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        try {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                if (isset($data['id'])) {
                    $repository = $this->getDoctrine()->getRepository(Representative::class);

                    $representative = $repository->findOneByUnid($data['id']);

                    $forbiddenRepresentative = new ForbiddenRepresentative();
                    $forbiddenRepresentative->setLogin($this->getUser()->getLogin());
                    $forbiddenRepresentative->setCreated(new \DateTime());
                    $forbiddenRepresentative->setName($representative->getName());

                    $manager = $this->getDoctrine()->getManager();

                    $manager->persist($forbiddenRepresentative);
                    $manager->flush();

                    $this->removeRepresentative($representative);
                    $api = new WpAPIService($this->getDoctrine());
                    $api->deleteRepresentative($representative);
                }
            }

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/data/representative-need-verify", name="representative-need-verify")
     */
    public function needVerify(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Representative::class);

        $filter = $request->query->all();

        if (!isset($filter['unable_to_verify']) || "false" === $filter['unable_to_verify']) {
            $filter['verification'] = true;
        }
        $filter['deleted'] = false;
        $representatives = $repository->findRepresentativesByFilterForExport($filter);

        $result = [];
        foreach ($representatives as $representative) {
            /** @var $representative Representative */
            $result[] = [
                'id'                       => $representative->getUnid(),
                'type'                     => $representative->getTypeName(),
                'name'                     => $representative->getName(),
                'company'                  => $representative->getCompanyName(),
                'status'                   => $representative->getStatus(),
                'last_verified'            => $representative->getLastVerified()->format("m/d/Y"),
                'verify_rank'              => $representative->getNeedsVerifyFlag(),
                'wp_id'                    => $representative->getWpId(),
                'categories'               => $representative->getCategoryNames(),
                'highest_ranked_celebrity' => $representative->getHighestRankedCelebrityNameAndRank()
            ];
        }


        return $this->json(['data' => $result, 'total' => count($representatives)]);
    }

    protected function createCompany($data, $companyName)
    {
        $manager = $this->getDoctrine()->getManager();
        $crepository = $this->getDoctrine()->getRepository(Category::class);

        $company = new Company();
        $company->setCreated(new \DateTime());
        $company->setWpId(0);
        $company->setUser($this->getUser());
        $company->setLastUpdatedAt(new \DateTime());


        if (isset($data['status'])) {
            $company->setStatus($data['status']);
        }

        $company->setName($companyName);

        if (isset($data['primaryCategory']) && isset($data['primaryCategory']['id'])) {
            $category = $crepository->find($data['primaryCategory']['id']);
            $company->setPrimaryCategory($category);
        }

        $location = new Location();
        $location->setName('Default address');
        if (isset($data['visitor_address'])) {
            $location->setVisitorAddress($data['visitor_address']);
        }
        if (isset($data['mailing_address'])) {
            $location->setMailingAddress($data['mailing_address']);
        }
        $company->addLocation($location);
        $manager->persist($location);

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
        $manager->persist($company);

        return $company;
    }
}
