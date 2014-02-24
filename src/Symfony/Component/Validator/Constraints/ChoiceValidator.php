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
 * Symfony_Component_Validator_Constraints_ChoiceValidator validates that the value is one of the expected values.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class Symfony_Component_Validator_Constraints_ChoiceValidator extends Symfony_Component_Validator_ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Symfony_Component_Validator_Constraint $constraint)
    {
        if (!$constraint->choices && !$constraint->callback) {
            throw new Symfony_Component_Validator_Exception_ConstraintDefinitionException('Either "choices" or "callback" must be specified on constraint Choice');
        }

        if (null === $value) {
            return;
        }

        if ($constraint->multiple && !is_array($value)) {
            throw new Symfony_Component_Validator_Exception_UnexpectedTypeException($value, 'array');
        }

        if ($constraint->callback) {
            if (is_callable(array($this->context->getClassName(), $constraint->callback))) {
                $choices = call_user_func(array($this->context->getClassName(), $constraint->callback));
            } elseif (is_callable($constraint->callback)) {
                $choices = call_user_func($constraint->callback);
            } else {
                throw new Symfony_Component_Validator_Exception_ConstraintDefinitionException('The Choice constraint expects a valid callback');
            }
        } else {
            $choices = $constraint->choices;
        }

        if ($constraint->multiple) {
            foreach ($value as $_value) {
                if (!in_array($_value, $choices, $constraint->strict)) {
                    $this->context->addViolation($constraint->multipleMessage, array('{{ value }}' => $_value));
                }
            }

            $count = count($value);

            if ($constraint->min !== null && $count < $constraint->min) {
                $this->context->addViolation($constraint->minMessage, array('{{ limit }}' => $constraint->min), null, (int) $constraint->min);

                return;
            }

            if ($constraint->max !== null && $count > $constraint->max) {
                $this->context->addViolation($constraint->maxMessage, array('{{ limit }}' => $constraint->max), null, (int) $constraint->max);

                return;
            }
        } elseif (!in_array($value, $choices, $constraint->strict)) {
            $this->context->addViolation($constraint->message, array('{{ value }}' => $value));
        }
    }
}
