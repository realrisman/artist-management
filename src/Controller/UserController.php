<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationInterface;
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
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'role' => $user->getRole(),
                'deleted' => $user->getDeleted(),
            ];
        }

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
                $service->updateUserById($data['id'], $data['username'], $data['password'], $data['role'], $data['deleted']);
            } else {
                $service->createUser($data['username'], $data['password'], $data['role']);
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
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'role' => $user->getRole(),
            'deleted' => $user->getDeleted(),
        ];


        return $this->json($result);
    }
}
