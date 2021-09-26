<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Celebrity;
use App\Entity\CelebrityLog;
use App\Entity\Company;
use App\Entity\ContactChange;
use App\Entity\Link;
use App\Entity\Representative;
use App\Entity\RepresentativeConnection;
use App\Entity\RepresentativeLog;
use App\Entity\Status;
use App\Service\WpAPIService;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CelebrityController extends AbstractController
{

    /**
     * @Route("/data/celebrity/{unid}", name="celebrity_details")
     * @param $unid
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function details($unid)
    {

        $this->denyAccessUnlessGranted('ROLE_IMAGE_UPLOADER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Celebrity::class);
        $logRepository = $this->getDoctrine()->getRepository(CelebrityLog::class);

        $celebrity = $repository->findOneByUnid($unid);


        if (!is_null($celebrity)) {
            $result = $celebrity->jsonSerialize();
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
     * @Route("/data/celebrity-log/{unid}", name="celebrity_log")
     */
    public function log($unid)
    {

        $this->denyAccessUnlessGranted('ROLE_TRAINER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(CelebrityLog::class);

        $logs = $repository->findBy(['unid' => $unid], ['date' => 'DESC']);

        $result = [];
        foreach ($logs as $celebrityLog) {
            /**
             * @var $celebrityLog CelebrityLog
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
     * @Route("/data/celebrity", name="celebrity", methods={"GET"})
     */
    public function index(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_WRITER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Celebrity::class);

        $celebrities = $repository->findByFilter($request->query->all());

        $result = [];
        foreach ($celebrities as $celebrity) {
            $result[] = [
                'id'     => $celebrity->getUnid(),
                'name'   => $celebrity->getName(),
                'status' => $celebrity->getStatus(),
                'wp_id'  => $celebrity->getWpId(),
            ];
        }

        return $this->json($result);
    }

    /**
     * Updates celebrity needs update flag
     * @Route("/data/celebrity-verify/{id}", name="celebrity_verify", methods={"POST"})
     */
    public function celebrityVerify($id, Connection $connection)
    {

        $this->denyAccessUnlessGranted('ROLE_WRITER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Celebrity::class);
        $celebrity = $repository->findOneByUnid($id);
        $celebrity->addVerificationLog($this->getUser()->getLogin());
        $logs = $celebrity->getVerificationLog();

        $today = new \DateTime();
        $connection->update('celebrity', [
            'last_verified'     => $today->format("Y-m-d"),
            'needs_verify_flag' => 0,
            'verification_log'  => $logs
        ], [
            'unid' => $id
        ]);

        $data = ['success' => true];

        return $this->json($data);
    }

    /**
     * @Route("/data/celebrity", name="celebrity_save", methods={"POST"})
     */
    public function save(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        error_log("Controller: starting saving celebrity");
        $this->denyAccessUnlessGranted('ROLE_WRITER', null, 'Unable to access this page!');

        $data = [];
        try {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
            }
            $repository = $this->getDoctrine()->getRepository(Celebrity::class);
            $rrepository = $this->getDoctrine()->getRepository(Representative::class);
            $rcrepository = $this->getDoctrine()->getRepository(RepresentativeConnection::class);
            $crepository = $this->getDoctrine()->getRepository(Category::class);
            $lrepository = $this->getDoctrine()->getRepository(Link::class);
            $comrepository = $this->getDoctrine()->getRepository(Company::class);
            $manager = $this->getDoctrine()->getManager();


            if (!empty($data['id'])) {
                $celebrity = $repository->findOneByUnid($data['id']);
            } else {
                //check if representative is allowed to be added
                $blocked = $repository->findOneBy(['name' => $data['name'], 'status' => Status::DELETED]);
                if (!is_null($blocked)) {
                    return $this->json(['success' => false, 'error' => 'Celebrity is on forbidden list: ' . $blocked->getRemoveReason(), 'blocked' => true]);
                }


                $search = [];
                if (!empty($data['birthdate'])) {
                    $birthdate = new \DateTime($data['birthdate']);
                    $search['birthdate'] = $birthdate;
                } else {
                    $search['birthdate'] = null;
                }
                $search['name'] = $data['name'];
                $celebrity = $repository->findOneBy($search);
                if (is_null($celebrity)) {
                    $celebrity = new Celebrity();
                    $celebrity->setCreated(new \DateTime());
                    $celebrity->setWpId(0);
                    $celebrity->setUser($this->getUser());
                }
            }

            $log = new CelebrityLog();

            $log->setUser($this->getUser());
            $log->setDate(new \DateTime());
            $log->setOld(json_encode($celebrity->logSerialize()));
            if ($data['spot_checked'] != $celebrity->getSpotChecked())
                $log->setSpotChecked(true);

            //writer can only these fields
            //@see https://app.asana.com/0/1158207458848354/1169885480004985
            if ($authChecker->isGranted('ROLE_WRITER')) {
                if (isset($data['bio'])) {
                    $celebrity->setBio($data['bio']);
                }
                if (isset($data['city'])) {
                    $celebrity->setCity($data['city']);
                }
                if (isset($data['state'])) {
                    $celebrity->setState($data['state']);
                }
                if (isset($data['country'])) {
                    $celebrity->setCountry($data['country']);
                }
                if (isset($data['profession'])) {
                    $celebrity->setProfession($data['profession']);
                }
                if (isset($data['youtube'])) {
                    $celebrity->setYoutube($data['youtube']);
                }
                if (!empty($data['birthdate'])) {
                    $birthdate = new \DateTime($data['birthdate']);
                    $celebrity->setBirthdate($birthdate);
                } elseif (is_null($data['birthdate'])) {
                    //allow to drop birthdate
                    $celebrity->setBirthdate(null);
                }
                if (isset($data['image'])) {
                    $celebrity->setImage($data['image']);
                }
            }

            $editedReps = 0;
            if ($authChecker->isGranted('ROLE_TRAINER')) {

                if (isset($data['status'])) {
                    if ($data['status'] != Status::LIVE) {
                        $celebrity->setStatus($data['status']);
                    }
                    if ($authChecker->isGranted('ROLE_EDITOR')) {
                        $celebrity->setStatus($data['status']);
                    }
                }
                if (isset($data['source'])) {
                    $celebrity->setSource($data['source']);
                } else {
                    $celebrity->setSource('');
                }
                if (isset($data['directAddress'])) {
                    $celebrity->setDirectAddress($data['directAddress']);
                }
                if (isset($data['name'])) {
                    $celebrity->setName($data['name']);
                }
                if (isset($data['price'])) {
                    $celebrity->setPrice($data['price']);
                }
                if (isset($data['instagram'])) {
                    $celebrity->setInstagram($data['instagram']);
                }
                if (isset($data['image_alt'])) {
                    $celebrity->setImageAlt($data['image_alt']);
                }
                if (isset($data['image'])) {
                    $celebrity->setImage($data['image']);
                }
                if (isset($data['deceased'])) {
                    $celebrity->setDeceased($data['deceased']);
                }
                if (isset($data['selfManaged'])) {
                    $celebrity->setSelfManaged($data['selfManaged']);
                }
                if (isset($data['hiatus'])) {
                    $celebrity->setHiatus($data['hiatus']);
                }
                if (isset($data['unable_to_verify'])) {
                    $celebrity->setUnableToVerify($data['unable_to_verify']);
                }
                if (isset($data['spot_checked'])) {
                    $celebrity->setSpotChecked($data['spot_checked']);
                }

                if (isset($data['image_title'])) {
                    $celebrity->setImageTitle($data['image_title']);
                }
                if (isset($data['primaryCategory']) && isset($data['primaryCategory']['id'])) {
                    $category = $crepository->find($data['primaryCategory']['id']);
                    $celebrity->setPrimaryCategory($category);
                }
                if (isset($data['remove_reason'])) {
                    $celebrity->setRemoveReason($data['remove_reason']);
                }

                $celebrity->setUser($this->getUser());
                $celebrity->setValidFrom(new \DateTime());

                $processedLinkIds = [];
                if (isset($data['links'])) {
                    if (is_array($data['links'])) {
                        foreach ($data['links'] as $l) {
                            if (!empty($l['id'])) {
                                $link = $lrepository->find($l['id']);
                            } else {
                                $link = new Link();
                                $link->setDeleted(0);
                            }
                            $link->setText($l['text']);
                            $link->setType($l['type']);
                            $link->setUrl($l['url']);

                            $manager->persist($link);

                            $celebrity->addLink($link);
                            $processedLinkIds[] = $link->getId();
                        }
                    }
                }
                //iterate over celebrity links and remove those not received from server
                foreach ($celebrity->getLinks() as $link) {
                    if (!is_null($link->getId()) && !in_array($link->getId(), $processedLinkIds)) {
                        $celebrity->removeLink($link);
                        $manager->remove($link);
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
                                $celebrity->addCategory($category);
                            }
                        }
                    }
                }
                $cats = $celebrity->getCategory();
                foreach ($cats as $cat) {
                    if (!in_array($cat->getId(), $categoryIds)) {
                        $celebrity->removeCategory($cat);
                    }
                }

                if (isset($data['representatives'])) {
                    if (is_array($data['representatives'])) {
                        $representativeConnectionIds = array_map(function ($rc) {
                            return isset($rc['rc_id']) ? $rc['rc_id'] : null;
                        }, $data['representatives']);

                        $rcs = $celebrity->getRepresentativeConnections();
                        foreach ($rcs as $rc) {
                            if (!in_array($rc->getId(), $representativeConnectionIds)) {
                                if ($rc->isCompany()) {
                                    $rep = $rc->getCompany();
                                } else {
                                    $rep = $rc->getRepresentative();
                                }
                                $repLog = new RepresentativeLog();
                                $repLog->setUser($this->getUser());
                                $repLog->setUnid($rep->getUnid());
                                $repLog->setDate(new \DateTime());
                                $repLog->setOld(json_encode($rep->logSerialize()));

                                $celebrity->removeRepresentativeConnection($rc);
                                $rep->removeRepresentativeConnection($rc);
                                $manager->remove($rc);

                                $repLog->setNew(json_encode($rep->logSerialize()));
                                $manager->persist($repLog);
                                $editedReps++;
                            }
                        }

                        foreach ($data['representatives'] as $connection) {
                            if (!isset($connection['rc_id'])) {
                                if (!empty($connection['company'])) {
                                    $company = $comrepository->find($connection['company']['id']);
                                    if ($company) {
                                        $editedReps++;
                                        $repLog = new RepresentativeLog();
                                        $repLog->setUser($this->getUser());
                                        $repLog->setUnid($company->getId());
                                        $repLog->setDate(new \DateTime());
                                        $repLog->setOld(json_encode($company->logSerialize()));

                                        $rc = new RepresentativeConnection();
                                        $rc->setIsCompany(true);
                                        $celebrity->addRepresentativeConnection($rc);
                                        $rc->setCompany($company);
                                        $rc->setCelebrity($celebrity);
                                        $company->addRepresentativeConnection($rc);

                                        $repLog->setNew(json_encode($company->logSerialize()));
                                        $manager->persist($repLog);
                                    } else {
                                        continue;
                                    }
                                } else {
                                    $rep = $rrepository->findOneByUnid($connection['representative']['id']);
                                    if ($rep) {
                                        $editedReps++;
                                        $repLog = new RepresentativeLog();
                                        $repLog->setUser($this->getUser());
                                        $repLog->setUnid($rep->getUnid());
                                        $repLog->setDate(new \DateTime());
                                        $repLog->setOld(json_encode($rep->logSerialize()));

                                        $rc = new RepresentativeConnection();
                                        $celebrity->addRepresentativeConnection($rc);
                                        $rc->setRepresentative($rep);
                                        $rc->setCelebrity($celebrity);
                                        $rep->addRepresentativeConnection($rc);

                                        $repLog->setNew(json_encode($rep->logSerialize()));
                                        $manager->persist($repLog);
                                    } else {
                                        continue;
                                    }
                                }
                            } else {
                                $rc = $rcrepository->find($connection['rc_id']);
                                if (!$rc) {
                                    continue;
                                }
                                if ($this->get('security.authorization_checker')->isGranted('ROLE_TRAINER')) {
                                    if (isset($connection['verify']) && true === $connection['verify']) {
                                        $rc->setLastVerified(new \DateTime());
                                        $rc->setNeedsVerifyFlag(0);
                                        $rc->addVerificationLog($this->getUser()->getLogin());
                                    }
                                }
                            }
                            $rc->setType($connection['type']);
                            $rc->setTerritory($connection['territory']);
                            $rc->setPosition(intval($connection['position']));

                            $manager->persist($rc);
                        }
                    }
                }
            }
            $filename = str_replace(" ", "-", $celebrity->getName()) . "-Contact-Information";
            $filename = str_replace('"', '', $filename);
            $filename = str_replace("'", "", $filename);

            if ($celebrity->getStatus() == Status::LIVE) {
                if ("" == $celebrity->getImage()) {
                    $avatar = new UploadedFile(__DIR__ . "/../../public/assets/default_celebrity_picture.png", $filename . ".png");
                    if (!isset($api)) {
                        $api = new WpAPIService($this->getDoctrine());
                    }
                    $response = $api->uploadFile($avatar);

                    $celebrity->setImage($response['source_url']);
                    $data['attachment_id'] = $response['id'];
                }
            }

            if ("" == $celebrity->getImageAlt()) {
                $celebrity->setImageAlt($filename);
            }
            if ("" == $celebrity->getImageTitle()) {
                $celebrity->setImageTitle(str_replace("-", " ", $filename));
            }

            if ($this->get('security.authorization_checker')->isGranted('ROLE_TRAINER')) {
                if (true === $data['verify']) {
                    $celebrity->setNeedsVerifyFlag(0);
                    $celebrity->setLastVerified(new \DateTime());
                    $celebrity->addVerificationLog($this->getUser()->getLogin());
                    $celebrity->setUnableToVerify(false);
                }
            }
            error_log("before persist");
            $manager->persist($celebrity);
            error_log("after persists 1");
            if ($celebrity->getStatus() != Status::QA && $celebrity->getStatus() != Status::READY) {
                $api = new WpAPIService($this->getDoctrine());
                $wp_id = $api->saveCelebrity($celebrity);
                $celebrity->setWpId($wp_id);
                $manager->persist($celebrity);
                error_log("after persists 2");
                if (!empty($celebrity->getImage()) && empty($data['attachment_id'])) {
                    $data['attachment_id'] = $api->getAttachmentIdByUrl($celebrity->getImage());
                }
                if (isset($data['attachment_id']) && false !== $data['attachment_id']) {
                    $api->setFeaturedImage($celebrity, $data['attachment_id']);
                }
            }


            if (isset($log)) {
                $log->setUnid($celebrity->getUnid());
                $log->setNew(json_encode($celebrity->logSerialize()));
                $manager->persist($log);
            }

            if (0 != $editedReps && $celebrity->getStatus() == Status::LIVE) {
                $cc = new ContactChange();
                $cc->setUnid(0);
                $cc->setCelebrities($celebrity->getUnid());
                $cc->setAuthor($this->getUser());
                $manager->persist($cc);
            }
            error_log("before flush");
            $manager->flush();
            error_log("after flush ");
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
     * @Route("/data/celebrity-image-uploader", name="celebrity_save_image_uploader", methods={"POST"})
     */
    public function saveImageDetails(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_IMAGE_UPLOADER', null, 'Unable to access this page!');

        $data = [];
        try {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
            }
            $repository = $this->getDoctrine()->getRepository(Celebrity::class);
            $manager = $this->getDoctrine()->getManager();

            if (!empty($data['id'])) {
                $celebrity = $repository->findOneByUnid($data['id']);
                $log = new CelebrityLog();

                $log->setUser($this->getUser());
                $log->setUnid($celebrity->getUnid());
                $log->setDate(new \DateTime());
                $log->setOld(json_encode($celebrity->logSerialize()));
            } else {
                $celebrity = new Celebrity();
                $celebrity->setCreated(new \DateTime());
                $celebrity->setWpId(0);
            }
            if (isset($data['source'])) {
                $celebrity->setSource($data['source']);
            } else {
                $celebrity->setSource('');
            }

            if (isset($data['image_alt'])) {
                $celebrity->setImageAlt($data['image_alt']);
            }
            if (isset($data['image'])) {
                $celebrity->setImage($data['image']);
            }

            if (isset($data['image_title'])) {
                $celebrity->setImageTitle($data['image_title']);
            }

            $filename = str_replace(" ", "-", $celebrity->getName()) . "-Contact-Information";
            $filename = str_replace('"', '', $filename);
            $filename = str_replace("'", "", $filename);

            if ("" == $celebrity->getImageAlt()) {
                $celebrity->setImageAlt($filename);
            }
            if ("" == $celebrity->getImageTitle()) {
                $celebrity->setImageTitle(str_replace("-", " ", $filename));
            }

            $manager->persist($celebrity);

            if ($celebrity->getStatus() != Status::QA && $celebrity->getStatus() != Status::READY) {
                $api = new WpAPIService($this->getDoctrine());
                $wp_id = $api->saveCelebrity($celebrity);
                $celebrity->setWpId($wp_id);
                $manager->persist($celebrity);
            }

            if (isset($data['attachment_id'])) {
                if (!isset($api)) {
                    $api = new WpAPIService($this->getDoctrine());
                }
                $api->setFeaturedImage($celebrity, $data['attachment_id']);
            }
            if (isset($log)) {
                $log->setNew(json_encode($celebrity->logSerialize()));
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
     * @Route("/data/celebrity-quick", name="celebrity-quick")
     */
    public function quick(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Celebrity::class);
        $filter = $request->query->all();

        if ($this->get('security.authorization_checker')->isGranted('ROLE_IMAGE_UPLOADER')) {
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_WRITER')) {
                //                $filter['status'] = 'draft';
            }
        }
        $celebrities = $repository->findByFilter($filter);


        $result = [];
        foreach ($celebrities as $celebrity) {
            /** @var  $celebrity Celebrity */
            $reps = [
                'agent'     => [],
                'manager'   => [],
                'publicist' => [],
            ];
            $representatives = $celebrity->getRepresentativeConnections();
            foreach ($representatives as $r) {
                $reps[$r->getType()][] = $r->getConnectedName();
            }
            $categories = [];
            $cats = $celebrity->getCategory();
            foreach ($cats as $cat) {
                $categories[] = $cat->getName();
            }
            $result[] = [
                'id'              => $celebrity->getUnid(),
                'name'            => $celebrity->getName(),
                'status'          => $celebrity->getStatus(),
                'bio'             => $celebrity->getBio(),
                'profession'      => $celebrity->getProfession(),
                'price'           => $celebrity->getPrice(),
                'country'         => $celebrity->getCountry(),
                'state'           => $celebrity->getState(),
                'city'            => $celebrity->getCity(),
                'wp_id'           => $celebrity->getWpId(),
                'representatives' => $reps,
                'categories'      => join(", ", $categories),
            ];
        }


        return $this->json(['data' => $result, 'total' => count($celebrities)]);
    }

    /**
     * @Route("/data/celebrity-need-verify", name="celebrity-need-verify")
     */
    public function needVerify(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Celebrity::class);
        $filter = $request->query->all();

        if (!isset($filter['unable_to_verify']) || "false" === $filter['unable_to_verify']) {
            $filter['verification'] = true;
        }
        $filter['alive'] = true;
        $filter['deleted'] = false;

        $celebrities = $repository->findByFilter($filter);

        $result = [];
        foreach ($celebrities as $celebrity) {

            $result[] = [
                'id'            => $celebrity->getUnid(),
                'name'          => $celebrity->getName(),
                'status'        => $celebrity->getStatus(),
                'bio'           => $celebrity->getBio(),
                'profession'    => $celebrity->getProfession(),
                'price'         => $celebrity->getPrice(),
                'country'       => $celebrity->getCountry(),
                'state'         => $celebrity->getState(),
                'city'          => $celebrity->getCity(),
                'wp_id'         => $celebrity->getWpId(),
                'last_verified' => $celebrity->getLastVerified()->format("m/d/Y"),
                'verify_rank'   => $celebrity->getNeedsVerifyFlag()
            ];
        }


        return $this->json(['data' => $result, 'total' => count($celebrities)]);
    }

    /**
     * @Route("/data/celebrity-full", name="celebrity-full")
     */
    public function full(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(Celebrity::class);

        $celebrities = $repository->findByFilter($request->query->all());


        $result = [];
        foreach ($celebrities as $celebrity) {
            /**
             * @var $celebrity Celebrity
             */
            $reps = [
                'agent'     => [],
                'manager'   => [],
                'publicist' => [],
            ];
            $representatives = $celebrity->getRepresentativeConnections();
            foreach ($representatives as $r) {
                $reps[$r->getType()][] = $r->getRepresentative()->jsonSerialize();
            }
            $categories = [];
            $cats = $celebrity->getCategory();
            foreach ($cats as $cat) {
                $categories[] = $cat->getName();
            }
            $birthday = false;
            if ($celebrity->getBirthdate()) {
                $birthday = $celebrity->getBirthdate()->format("m/d/Y");
            }
            $result[] = [
                'id'              => $celebrity->getUnid(),
                'name'            => $celebrity->getName(),
                'status'          => $celebrity->getStatus(),
                'bio'             => $celebrity->getBio(),
                'birthdate'       => $birthday,
                'profession'      => $celebrity->getProfession(),
                'price'           => $celebrity->getPrice(),
                'country'         => $celebrity->getCountry(),
                'state'           => $celebrity->getState(),
                'city'            => $celebrity->getCity(),
                'wp_id'           => $celebrity->getWpId(),
                'representatives' => $reps,
                'categories'      => join(", ", $categories),
            ];
        }


        return $this->json(['data' => $result, 'total' => count($celebrities)]);
    }

    /**
     * @Route("/data/celebrity-delete", name="celebrity_delete", methods={"POST"})
     */
    public function delete(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        try {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                if (isset($data['id'])) {
                    $repository = $this->getDoctrine()->getRepository(Celebrity::class);

                    $celebrities = $repository->findBy(['unid' => $data['id']]);

                    $this->removeCelebrities($celebrities);

                    $api = new WpAPIService($this->getDoctrine());
                    $api->deleteCelebrity($celebrities[0]);
                }
            }

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/data/celebrity-import", name="celebrity_import",methods={"POST"})
     */
    public function import(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_SPECTATOR', null, 'Unable to access this page!');

        try {
            $response = ['success' => true];
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                if (!empty($data['id'])) {
                    $repository = $this->getDoctrine()->getRepository(Celebrity::class);
                    $celebrity = $repository->findOneByUnid($data['id']);
                    $api = new WpAPIService($this->getDoctrine());
                    $celebrity = $api->importCelebrity($celebrity);
                    $manager = $this->getDoctrine()->getManager();
                    if ($celebrity instanceof Celebrity) {
                        if ($celebrity->getValidTill() != new \DateTime('2099-12-31')) {
                            $this->removeCelebrities([$celebrity]);
                            $response = ['success' => true, 'removed' => true];
                        } else {
                            $celebrity->setUser($this->getUser());
                            $manager->persist($celebrity);

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

    /**
     * @param array $celebrities
     */
    protected function removeCelebrities(array $celebrities): void
    {
        $manager = $this->getDoctrine()->getManager();

        foreach ($celebrities as $celebrity) {
            foreach ($celebrity->getRepresentativeConnections() as $connection) {
                $manager->remove($connection);
            }
            foreach ($celebrity->getLinks() as $link) {
                $manager->remove($link);
            }

            $manager->remove($celebrity);
        }
        $manager->flush();
    }
}
