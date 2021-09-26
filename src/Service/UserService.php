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


    public function createUser($username, $password, $role)
    {
        $user = new User();

        $user->setActive(1);
        $user->setUsername($username);
        $user->setPassword($this->encoder->encodePassword($user, $password));
        $user->setRole($role);

        $this->manager->persist($user);
        $this->manager->flush();
    }

    public function updateUser($username, $password = '', $role)
    {
        $user = $this->findUserByUsername($username);
        $this->updateUserData($user, $username, $password, $role);
    }

    public function updateUserById($id, $username, $password = '', $role, $deleted = false)
    {
        $user = $this->repository->find($id);

        if (!$user) {
            throw new \Exception('User not found');
        }

        $this->updateUserData($user, $username, $password, $role, $deleted);
    }

    public function findUserByUsername($username)
    {
        return $this->repository->findOneBy(['username' => $username]);
    }

    public function login($username, $password)
    {
        $user = $this->findUserByUsername($username);
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
     * @param bool $deleted
     * @throws \Exception
     */
    protected function updateUserData(User $user, $username, $password, $role, $deleted = false): void
    {
        if ($password) {
            $user->setPassword($this->encoder->encodePassword($user, $password));
        }
        $user->setRole($role);
        $user->setUsername($username);
        $user->setDeleted($deleted);

        $this->manager->persist($user);
        $this->manager->flush();
    }
}
