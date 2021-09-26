<?php

namespace App\Service;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{

    protected $manager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var UserValidatorService
     */
    private $validator;

    /**
     * UserService constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder, UserRepository $repository, UserValidatorService $validator)
    {
        $this->manager    = $entityManager;
        $this->encoder    = $encoder;
        $this->repository = $repository;
        $this->validator  = $validator;
    }


    public function createUser($login, $password, $role, $first_name = '', $last_name = '')
    {
        $user = new User();

        $user->setActive(1);
        $user->setLogin($login);
        $user->setPassword($this->encoder->encodePassword($user, $password));
        $user->setRole($role);

        if (!empty($first_name)) {
            $user->setFirstName($first_name);
        }
        if (!empty($last_name)) {
            $user->setLastName($last_name);
        }
        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $this->manager->persist($user);

        // actually executes the queries (i.e. the INSERT query)
        $this->manager->flush();
    }

    public function updateUserById($id, $login, $password = '', $role, $first_name = '', $last_name = '', $deleted = false, $monthlyLimit, $limitUsed)
    {
        $user = $this->repository->find($id);

        if (!$user) {
            throw new \Exception('User not found');
        }

        $this->updateUserData($user, $login, $password, $role, $first_name, $last_name, $deleted, $monthlyLimit, $limitUsed);
    }

    public function findUserByLogin($login)
    {
        return $this->repository->findOneBy(['login' => $login]);
    }

    public function login($login, $password)
    {
        $user = $this->findUserByLogin($login);
        if (!$user) {
            throw new \Exception('User not found');
        }
        if (!$this->encoder->isPasswordValid($user, $password)) {
            throw new \Exception('Invalid password');
        }

        if ($user->getDeleted()) {
            throw new \Exception('User deleted');
        }

        return $user;
    }

    public function validate($user)
    {
        return $this->validator->validate($user);
    }

    /**
     * @param User $user
     * @param $login
     * @param $password
     * @param $role
     * @param string $first_name
     * @param string $last_name
     * @param bool $deleted
     * @throws \Exception
     */
    protected function updateUserData(User $user, $login, $password, $role, $first_name = '', $last_name = '', $deleted = false, $monthlyLimit, $limitUsed): void
    {
        if ($password) {
            $user->setPassword($this->encoder->encodePassword($user, $password));
        }
        $user->setRole($role);
        $user->setLogin($login);
        $user->setDeleted($deleted);
        if (!empty($first_name)) {
            $user->setFirstName($first_name);
        }
        if (!empty($last_name)) {
            $user->setLastName($last_name);
        }
        $user->setMonthlyLimit($monthlyLimit);
        $user->setLimitUsed($limitUsed);

        // tell Doctrine you want to (eventually) save User
        $this->manager->persist($user);

        // actually executes the queries (i.e. the INSERT query)
        $this->manager->flush();
    }
}
