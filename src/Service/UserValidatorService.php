<?php

namespace App\Service;

use App\Validators\PasswordsMatch;
use App\Validators\UniqueUsername;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserValidatorService
{

    protected $constraints;
    protected $validator;

    /**
     * UserValidatorService constructor.
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator   = $validator;
        $this->constraints = [
            new Assert\Collection([
                'fields'           => [
                    'username' => new Assert\NotBlank(),
                    'role'  => new Assert\Choice(array('ROLE_ADMIN', 'ROLE_EDITOR')),
                ],
                'allowExtraFields' => true
            ]),
            new UniqueUsername(),
            new PasswordsMatch()
        ];
    }


    public function validate($user)
    {
        $errors = $this->validator->validate($user, $this->constraints);

        return $errors;
    }
}
