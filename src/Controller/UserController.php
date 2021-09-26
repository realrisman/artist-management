<?php

namespace App\Controller;

use App\Entity\Email;
use App\Entity\User;
use App\Service\HunterAPIService;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    /**
     * @Route("/data/users", name="user_list", methods={"GET"})
     */
    public function index()
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $users      = $repository->findBy(['deleted' => false]);

        foreach ($users as $user) {
            $result[] = [
                'id'            => $user->getId(),
                'login'         => $user->getLogin(),
                'first_name'    => $user->getFirstName(),
                'last_name'     => $user->getLastName(),
                'role'          => $user->getRole(),
                'deleted'       => $user->getDeleted(),
                'monthly_limit' => $user->getMonthlyLimit(),
                'limit_used'    => $user->getLimitUsed()
            ];
        }

        return $this->json($result);
    }

    /**
     * @Route("/data/emails", name="email_list", methods={"GET"})
     */
    public function emails()
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $emailSyncStatus = '';
        $planName = '-';
        $used = '-';
        $available = '-';
        $repository             = $this->getDoctrine()->getRepository(Email::class);
        $emails                 = $repository->findBy(['deleted' => false]);
        $deliverableEmails      = $repository->findBy(['deleted' => false, 'result' => 'deliverable']);
        $riskyEmails            = $repository->findBy(['deleted' => false, 'result' => 'risky']);
        $undeliverable          = $repository->findBy(['deleted' => false, 'result' => 'undeliverable']);
        $invalid                = $repository->findBy(['deleted' => false, 'status' => 'invalid']);
        $newEmails              = $repository->findBy(['deleted' => false, 'lastUpdatedAt' => null]);
        $countDeliverableEmails = count($deliverableEmails);
        $countRiskyEmails       = count($riskyEmails);
        $countUndeliverable     = count($undeliverable);
        $countInvalid           = count($invalid);
        $totalEmails            = count($emails);
        $countNewEmails         = count($newEmails);
        $checkedEmails          = $countDeliverableEmails + $countRiskyEmails + $countUndeliverable + $countInvalid;
        $api   = new HunterAPIService($this->getDoctrine());
        $hunterInfo = $api->getHunterAccountInfo();
        if (isset($hunterInfo->data)) {
            $planName = $hunterInfo->data->plan_name;
            $used = $hunterInfo->data->calls->used;
            $available = $hunterInfo->data->calls->used . '/' . $hunterInfo->data->calls->available;
        }
        $apiKey = getenv('HUNTER_API_KEY');

        $user = $this->getUser();
        if ($user) {
            if ($user->getEmailSync() == 'completed') {
                $user->setEmailSync('');
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($user);
                $manager->flush();
                $emailSyncStatus = 'completed';
            }
        }
        $result = [
            'deliverableEmails' => $countDeliverableEmails,
            'riskyEmails' => $countRiskyEmails,
            'undeliverableEmails' => $countUndeliverable,
            'invalidEmails' => $countInvalid,
            'checkedEmails' => $checkedEmails,
            'totalEmails' => $totalEmails,
            'planName' => $planName,
            'used' => $used,
            'available' => $available,
            'apiKey' => $apiKey,
            'emailSyncStatus' => $emailSyncStatus,
            'newEmails' => $countNewEmails,
            'userHunterSyncStatus' => $user->getEmailSync(),
        ];


        return $this->json($result);
    }

    /**
     * @Route("/data/emails-sync", name="email-sync", methods={"GET"})
     */
    public function emailsSync(Request $request,  KernelInterface $kernel, Connection $conn)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        try {

            $userData = $this->getDoctrine()->getRepository(User::class)->find($this->getUser()->getId());
            $userId = '';
            if (!empty($userData)) {
                $userData->setEmailSync('inprogress');
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($userData);
                $manager->flush();
                $userId = $userData->getId();
            }
            $process = new \Symfony\Component\Process\Process('php bin/console app:verify-emails ' . $userId);
            $process->setWorkingDirectory(getcwd() . "/../");
            $process->start();
            $pid = $process->getPid();
            $result = [
                'status' => 'Processing',
                'pid' => $pid,
            ];
            while ($process->isRunning()) {
                return $this->json($result);
            }
            return $this->json($result);
        } catch (\RuntimeException $r) {
            $userData =  $this->getDoctrine()->getRepository(User::class)->find($this->getUser()->getId());
            if (!empty($userData)) {
                $userData->setEmailSync('inprogress');
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($userData);
                $manager->flush();
            }
            return $this->json($r->getMessage());
        }
    }

    /**
     * @Route("/data/emails-reset", name="email-reset", methods={"GET"})
     */
    public function resetEmailAddresses(Request $request,  KernelInterface $kernel, Connection $conn)
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        try {
            $userData = $this->getDoctrine()->getRepository(User::class)->find($this->getUser()->getId());
            if (!empty($userData)) {
                $queryBuilder = $conn->createQueryBuilder();
                $queryBuilder = $queryBuilder->update('email')
                    ->set('status',  $queryBuilder->createNamedParameter(null))
                    ->set('result',  $queryBuilder->createNamedParameter(null));
                $queryBuilder->execute();
                $result = [
                    'status' => 'success',
                ];
            }
            return $this->json($result);
        } catch (\RuntimeException $r) {
            return $this->json($r->getMessage());
        }
    }

    /**
     * @Route("/data/stop-running-process", name="stop-running-process", methods={"GET"})
     */
    public function stopRunningProcess(Request $request)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser()->getId());
        $manager = $this->getDoctrine()->getManager();
        $user->setEmailSync('stop');
        $manager->persist($user);
        $manager->flush();
        $result = [
            'status' => 'success',
            'message' => 'process stopped successfully.'
        ];
        return $this->json($result);
    }

    /**
     * @Route("/data/users", name="user_save", methods={"POST"})
     */
    public function save(Request $request, UserService $service)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
        }

        $errors = $service->validate($data);
        if (count($errors) > 0) {
            $json_errors = [];
            foreach ($errors as $error_item) {
                /**
                 * @var ConstraintViolationInterface $error_item
                 */
                $field_name               = str_replace(array("[", "]"), '', $error_item->getPropertyPath());
                $json_errors[$field_name] = array('text' => $error_item->getMessage());
            }

            return $this->json([
                'success' => false,
                'errors'  => $json_errors
            ]);
        }

        try {
            if (!empty($data['id'])) {
                if ("" == $data['monthly_limit']) {
                    $data['monthly_limit'] = null;
                }
                $service->updateUserById($data['id'], $data['login'], $data['password'], $data['role'], $data['first_name'], $data['last_name'], $data['deleted'], $data['monthly_limit'], $data['limit_used']);
            } else {
                $service->createUser($data['login'], $data['password'], $data['role'], $data['first_name'], $data['last_name']);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'errors'  => [$e->getMessage()]
            ]);
        }

        return $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * @Route("/data/users/{id}", name="user_info", methods={"GET"})
     */
    public function info($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $user       = $repository->find($id);

        $result = [
            'id'            => $user->getId(),
            'login'         => $user->getLogin(),
            'role'          => $user->getRole(),
            'first_name'    => $user->getFirstName(),
            'last_name'     => $user->getLastName(),
            'deleted'       => $user->getDeleted(),
            'monthly_limit' => $user->getMonthlyLimit(),
            'limit_used'    => $user->getLimitUsed()
        ];


        return $this->json($result);
    }

    /**
     * @Route("/data/user-list", name="user_list_for_filter", methods={"GET"})
     */
    public function list()
    {
        $this->denyAccessUnlessGranted('ROLE_TRAINER', null, 'Unable to access this page!');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $users      = $repository->findBy([], ['login' => 'asc']);

        foreach ($users as $user) {
            $result[] = [
                'id'         => $user->getId(),
                'login'      => $user->getLogin(),
                'first_name' => $user->getFirstName(),
                'last_name'  => $user->getLastName(),

            ];
        }

        return $this->json($result);
    }
}
