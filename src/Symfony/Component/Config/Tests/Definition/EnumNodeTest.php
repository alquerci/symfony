<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Definition_EnumNodeTest extends PHPUnit_Framework_TestCase
{
    public function testFinalizeValue()
    {
        $node = new Symfony_Component_Config_Definition_EnumNode('foo', null, array('foo', 'bar'));
        $this->assertSame('foo', $node->finalize('foo'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructionWithOneValue()
    {
        new Symfony_Component_Config_Definition_EnumNode('foo', null, array('foo', 'foo'));
    }

    /**
     * @expectedException Symfony_Component_Config_Definition_Exception_InvalidConfigurationException
     * @expectedExceptionMessage The value "foobar" is not allowed for path "foo". Permissible values: "foo", "bar"
     */
    public function testFinalizeWithInvalidValue()
    {
        $node = new Symfony_Component_Config_Definition_EnumNode('foo', null, array('foo', 'bar'));
        $node->finalize('foobar');
    }
}
