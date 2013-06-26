<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @Annotation
 */
class Symfony_Component_Security_Core_Validator_Constraints_UserPassword extends Symfony_Component_Validator_Constraint_Constraint
{
    public $message = 'This value should be the user current password.';
    public $service = 'security.validator.user_password';

    public function validatedBy()
    {
        return $this->service;
    }
}
