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
class Symfony_Component_Validator_Tests_Constraints_AllTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testRejectNonConstraints()
    {
        new Symfony_Component_Validator_Constraints_All(array(
            'foo',
        ));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testRejectValidConstraint()
    {
        new Symfony_Component_Validator_Constraints_All(array(
            new Symfony_Component_Validator_Constraints_Valid(),
        ));
    }
}
