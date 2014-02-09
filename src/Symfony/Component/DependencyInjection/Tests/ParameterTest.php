<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_ParameterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Symfony_Component_DependencyInjection_Parameter::__construct
     */
    public function testConstructor()
    {
        $ref = new Symfony_Component_DependencyInjection_Parameter('foo');
        $this->assertEquals('foo', (string) $ref->__toString(), '__construct() sets the id of the parameter, which is used for the __toString() method');
    }
}
