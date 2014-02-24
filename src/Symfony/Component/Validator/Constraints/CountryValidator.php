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
 * Validates whether a value is a valid country code
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class Symfony_Component_Validator_Constraints_CountryValidator extends Symfony_Component_Validator_ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Symfony_Component_Validator_Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new Symfony_Component_Validator_Exception_UnexpectedTypeException($value, 'string');
        }

        $value = (string) $value;

        if (!in_array($value, Symfony_Component_Locale_Locale::getCountries())) {
            $this->context->addViolation($constraint->message, array('{{ value }}' => $value));
        }
    }
}
