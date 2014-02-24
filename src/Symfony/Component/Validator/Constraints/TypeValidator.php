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
class Symfony_Component_Validator_Constraints_TypeValidator extends Symfony_Component_Validator_ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Symfony_Component_Validator_Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        $type = strtolower($constraint->type);
        $type = $type == 'boolean' ? 'bool' : $constraint->type;
        $isFunction = 'is_'.$type;
        $ctypeFunction = 'ctype_'.$type;

        if (function_exists($isFunction) && call_user_func($isFunction, $value)) {
            return;
        } elseif (function_exists($ctypeFunction) && call_user_func($ctypeFunction, $value)) {
            return;
        } elseif ($value instanceof $constraint->type) {
            return;
        }

        $this->context->addViolation($constraint->message, array(
            '{{ value }}' => is_object($value) ? get_class($value) : (is_array($value) ? 'Array' : (string) $value),
            '{{ type }}'  => $constraint->type,
        ));
    }
}
