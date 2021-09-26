<?php

namespace App\Validators;

use Symfony\Component\Validator\Constraint;

class PasswordsMatch extends Constraint
{

    public $message = 'Passwords must match';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
