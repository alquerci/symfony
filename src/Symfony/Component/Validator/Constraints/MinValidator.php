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
 *
 * @deprecated Deprecated since version 2.1, to be removed in 2.3.
 */
class Symfony_Component_Validator_Constraints_MinValidator extends Symfony_Component_Validator_ConstraintValidator
{
    public function __construct($options = null)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('MinValidator is deprecated since version 2.1 and will be removed in 2.3.', E_USER_DEPRECATED);
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Symfony_Component_Validator_Constraint $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Symfony_Component_Validator_Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_numeric($value)) {
            $this->context->addViolation($constraint->invalidMessage, array(
                '{{ value }}' => $value,
                '{{ limit }}' => $constraint->limit,
            ));

            return;
        }

        if ($value < $constraint->limit) {
            $this->context->addViolation($constraint->message, array(
                '{{ value }}' => $value,
                '{{ limit }}' => $constraint->limit,
            ));
        }
    }
}
