<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Definition_BooleanNodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getValidValues
     */
    public function testNormalize($value)
    {
        $node = new Symfony_Component_Config_Definition_BooleanNode('test');
        $this->assertSame($value, $node->normalize($value));
    }

    public function getValidValues()
    {
        return array(
            array(false),
            array(true),
        );
    }

    /**
     * @dataProvider getInvalidValues
     * @expectedException Symfony_Component_Config_Definition_Exception_InvalidTypeException
     */
    public function testNormalizeThrowsExceptionOnInvalidValues($value)
    {
        $node = new Symfony_Component_Config_Definition_BooleanNode('test');
        $node->normalize($value);
    }

    public function getInvalidValues()
    {
        return array(
            array(null),
            array(''),
            array('foo'),
            array(0),
            array(1),
            array(0.0),
            array(0.1),
            array(array()),
            array(array('foo' => 'bar')),
            array(new stdClass()),
        );
    }
}
