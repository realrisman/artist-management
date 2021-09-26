<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="login_get", methods={"GET","HEAD"})
     */
    public function index()
    {

        return $this->json(
            [
                'message' => 'Please log in',
                'error'   => true
            ],
            200
        );
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(Request $request, UserService $userService)
    {
        $data = false;
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
        }

        if (isset($data['login']) && isset($data['password'])) {
            try {
                $user  = $userService->login($data['login'], $data['password']);
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);

                // If the firewall name is not main, then the set value would be instead:
                // $this->get('session')->set('_security_XXXFIREWALLNAMEXXX', serialize($token));
                $this->get('session')->set('_security_main', serialize($token));

                // Fire the login event manually
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
            } catch (\Exception $e) {
                return $this->json(
                    [
                        'message' => $e->getMessage(),
                        'data'    => $data
                    ],
                    401
                );
            }
        } else {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            $user = $this->getUser();
        }

        return $this->json(
            [
                'role'    => $user->getRole(),
                'success' => true,
                'data'    => $data
            ],
            200
        );
    }


    /**
     * @Route("/logout", name="logout", methods={"POST"})
     */
    public function logout(Request $request)
    {
        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        return $this->json([
            'success' => true
        ]);
    }
}
