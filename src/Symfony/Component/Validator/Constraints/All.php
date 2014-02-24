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
 * @Annotation
 *
 * @api
 */
class Symfony_Component_Validator_Constraints_All extends Symfony_Component_Validator_Constraint
{
    public $constraints = array();

    /**
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (!is_array($this->constraints)) {
            $this->constraints = array($this->constraints);
        }

        foreach ($this->constraints as $constraint) {
            if (!$constraint instanceof Symfony_Component_Validator_Constraint) {
                throw new Symfony_Component_Validator_Exception_ConstraintDefinitionException('The value ' . $constraint . ' is not an instance of Constraint in constraint ' . __CLASS__);
            }

            if ($constraint instanceof Symfony_Component_Validator_Constraints_Valid) {
                throw new Symfony_Component_Validator_Exception_ConstraintDefinitionException('The constraint Valid cannot be nested inside constraint ' . __CLASS__ . '. You can only declare the Valid constraint directly on a field or method.');
            }
        }
    }

    public function getDefaultOption()
    {
        return 'constraints';
    }

    public function getRequiredOptions()
    {
        return array('constraints');
    }
}
