<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Definition_FloatNodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getValidValues
     */
    public function testNormalize($value)
    {
        $node = new Symfony_Component_Config_Definition_FloatNode('test');
        $this->assertSame($value, $node->normalize($value));
    }

    public function getValidValues()
    {
        return array(
            array(1798.0),
            array(-678.987),
            array(12.56E45),
            array(0.0),
            // Integer are accepted too, they will be cast
            array(17),
            array(-10),
            array(0)
        );
    }

    /**
     * @dataProvider getInvalidValues
     * @expectedException Symfony_Component_Config_Definition_Exception_InvalidTypeException
     */
    public function testNormalizeThrowsExceptionOnInvalidValues($value)
    {
        $node = new Symfony_Component_Config_Definition_FloatNode('test');
        $node->normalize($value);
    }

    public function getInvalidValues()
    {
        return array(
            array(null),
            array(''),
            array('foo'),
            array(true),
            array(false),
            array(array()),
            array(array('foo' => 'bar')),
            array(new stdClass()),
        );
    }
}
