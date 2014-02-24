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
 * Default implementation of the Symfony_Component_Validator_ConstraintValidatorFactoryInterface.
 *
 * This enforces the convention that the validatedBy() method on any
 * Constrain will return the class name of the ConstraintValidator that
 * should validate the Symfony_Component_Validator_Constraint.
 */
class Symfony_Component_Validator_ConstraintValidatorFactory implements Symfony_Component_Validator_ConstraintValidatorFactoryInterface
{
    protected $validators = array();

    /**
     * {@inheritDoc}
     */
    public function getInstance(Symfony_Component_Validator_Constraint $constraint)
    {
        $className = $constraint->validatedBy();

        if (!isset($this->validators[$className])) {
            $this->validators[$className] = new $className();
        }

        return $this->validators[$className];
    }
}
