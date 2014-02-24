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
class Symfony_Component_Validator_Tests_Constraints_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testRejectInvalidFieldsOption()
    {
        new Symfony_Component_Validator_Constraints_Collection(array(
            'fields' => 'foo',
        ));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testRejectNonConstraints()
    {
        new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => 'bar',
        ));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testRejectValidConstraint()
    {
        new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Valid(),
        ));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testRejectValidConstraintWithinOptional()
    {
        new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Collection_Optional(new Symfony_Component_Validator_Constraints_Valid()),
        ));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testRejectValidConstraintWithinRequired()
    {
        new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Collection_Required(new Symfony_Component_Validator_Constraints_Valid()),
        ));
    }
}
