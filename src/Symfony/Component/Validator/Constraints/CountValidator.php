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
 */
class Symfony_Component_Validator_Constraints_CountValidator extends Symfony_Component_Validator_ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Symfony_Component_Validator_Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!is_array($value) && !$value instanceof Countable) {
            throw new Symfony_Component_Validator_Exception_UnexpectedTypeException($value, 'array or Countable');
        }

        $count = count($value);

        if ($constraint->min == $constraint->max && $count != $constraint->min) {
            $this->context->addViolation($constraint->exactMessage, array(
                '{{ count }}' => $count,
                '{{ limit }}' => $constraint->min,
            ), $value, (int) $constraint->min);

            return;
        }

        if (null !== $constraint->max && $count > $constraint->max) {
            $this->context->addViolation($constraint->maxMessage, array(
                '{{ count }}' => $count,
                '{{ limit }}' => $constraint->max,
            ), $value, (int) $constraint->max);

            return;
        }

        if (null !== $constraint->min && $count < $constraint->min) {
            $this->context->addViolation($constraint->minMessage, array(
                '{{ count }}' => $count,
                '{{ limit }}' => $constraint->min,
            ), $value, (int) $constraint->min);
        }
    }
}
