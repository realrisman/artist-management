<?php

namespace App\Validators;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueUsername extends Constraint
{

    public $message = 'Username "{{ string }}" is already used, please choose another';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
