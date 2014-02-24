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
 * Specifies an object able to return the correct ConstraintValidatorInterface
 * instance given a Constrain object.
 */
interface Symfony_Component_Validator_ConstraintValidatorFactoryInterface
{
    /**
     * Given a Symfony_Component_Validator_Constraint, this returns the ConstraintValidatorInterface
     * object that should be used to verify its validity.
     *
     * @param Symfony_Component_Validator_Constraint $constraint The source constraint
     *
     * @return Symfony_Component_Validator_ConstraintValidatorInterface
     */
    public function getInstance(Symfony_Component_Validator_Constraint $constraint);
}
