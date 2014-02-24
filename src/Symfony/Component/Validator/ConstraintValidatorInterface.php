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
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
interface Symfony_Component_Validator_ConstraintValidatorInterface
{
    /**
     * Initializes the constraint validator.
     *
     * @param Symfony_Component_Validator_ExecutionContextInterface $context The current validation context
     */
    public function initialize(Symfony_Component_Validator_ExecutionContextInterface $context);

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Symfony_Component_Validator_Constraint $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Symfony_Component_Validator_Constraint $constraint);
}
