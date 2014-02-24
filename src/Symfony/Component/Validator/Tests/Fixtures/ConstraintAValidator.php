<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Fixtures_ConstraintAValidator extends Symfony_Component_Validator_ConstraintValidator
{
    public static $passedContext;

    public function initialize(Symfony_Component_Validator_ExecutionContextInterface $context)
    {
        parent::initialize($context);

        self::$passedContext = $context;
    }

    public function validate($value, Symfony_Component_Validator_Constraint $constraint)
    {
        if ('VALID' != $value) {
            $this->context->addViolation('message', array('param' => 'value'));

            return;
        }

        return;
    }
}
