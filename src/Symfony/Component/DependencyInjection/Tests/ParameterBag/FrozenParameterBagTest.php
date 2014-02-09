<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_ParameterBag_FrozenParameterBagTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Symfony_Component_DependencyInjection_ParameterBag_FrozenParameterBag::__construct
     */
    public function testConstructor()
    {
        $parameters = array(
            'foo' => 'foo',
            'bar' => 'bar',
        );
        $bag = new Symfony_Component_DependencyInjection_ParameterBag_FrozenParameterBag($parameters);
        $this->assertEquals($parameters, $bag->all(), '__construct() takes an array of parameters as its first argument');
    }

    /**
     * @covers Symfony_Component_DependencyInjection_ParameterBag_FrozenParameterBag::clear
     * @expectedException LogicException
     */
    public function testClear()
    {
        $bag = new Symfony_Component_DependencyInjection_ParameterBag_FrozenParameterBag(array());
        $bag->clear();
    }

    /**
     * @covers Symfony_Component_DependencyInjection_ParameterBag_FrozenParameterBag::set
     * @expectedException LogicException
     */
    public function testSet()
    {
        $bag = new Symfony_Component_DependencyInjection_ParameterBag_FrozenParameterBag(array());
        $bag->set('foo', 'bar');
    }

    /**
     * @covers Symfony_Component_DependencyInjection_ParameterBag_FrozenParameterBag::add
     * @expectedException LogicException
     */
    public function testAdd()
    {
        $bag = new Symfony_Component_DependencyInjection_ParameterBag_FrozenParameterBag(array());
        $bag->add(array());
    }
}
