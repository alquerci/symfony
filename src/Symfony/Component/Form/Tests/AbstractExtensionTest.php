<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_AbstractExtensionTest extends PHPUnit_Framework_TestCase
{
    public function testHasType()
    {
        $loader = new Symfony_Component_Form_Tests_ConcreteExtension();
        $this->assertTrue($loader->hasType('foo'));
        $this->assertFalse($loader->hasType('bar'));
    }

    public function testGetType()
    {
        $loader = new Symfony_Component_Form_Tests_ConcreteExtension();
        $this->assertInstanceOf('Symfony_Component_Form_Tests_Fixtures_FooType', $loader->getType('foo'));
    }
}

class Symfony_Component_Form_Tests_ConcreteExtension extends Symfony_Component_Form_AbstractExtension
{
    protected function loadTypes()
    {
        return array(new Symfony_Component_Form_Tests_Fixtures_FooType());
    }

    protected function loadTypeGuesser()
    {
    }
}
