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
class Symfony_Component_Validator_Constraints_AllValidator extends Symfony_Component_Validator_ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Symfony_Component_Validator_Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!is_array($value) && !$value instanceof Traversable) {
            throw new Symfony_Component_Validator_Exception_UnexpectedTypeException($value, 'array or Traversable');
        }

        $group = $this->context->getGroup();

        foreach ($value as $key => $element) {
            foreach ($constraint->constraints as $constr) {
                $this->context->validateValue($element, $constr, '['.$key.']', $group);
            }
        }
    }
}
