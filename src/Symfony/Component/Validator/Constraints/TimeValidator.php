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
class Symfony_Component_Validator_Constraints_TimeValidator extends Symfony_Component_Validator_ConstraintValidator
{
    const PATTERN = '/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/';

    protected $pattern = self::PATTERN;

    /**
     * {@inheritDoc}
     */
    public function validate($value, Symfony_Component_Validator_Constraint $constraint)
    {
        if (null === $value || '' === $value || $value instanceof DateTime) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new Symfony_Component_Validator_Exception_UnexpectedTypeException($value, 'string');
        }

        $value = (string) $value;

        if (!preg_match($this->pattern, $value)) {
            $this->context->addViolation($constraint->message, array('{{ value }}' => $value));
        }
    }
}
