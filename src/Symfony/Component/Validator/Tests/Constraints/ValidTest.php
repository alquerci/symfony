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
class Symfony_Component_Validator_Tests_Constraints_ValidTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testRejectGroupsOption()
    {
        new Symfony_Component_Validator_Constraints_Valid(array('groups' => 'foo'));
    }
}
