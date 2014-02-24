<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_BooleanToStringTransformerTest extends PHPUnit_Framework_TestCase
{
    const TRUE_VALUE = '1';

    protected $transformer;

    protected function setUp()
    {
        $this->transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_BooleanToStringTransformer(self::TRUE_VALUE);
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function testTransform()
    {
        $this->assertEquals(self::TRUE_VALUE, $this->transformer->transform(true));
        $this->assertNull($this->transformer->transform(false));
        $this->assertNull($this->transformer->transform(null));
    }

    public function testTransformExpectsBoolean()
    {
        $this->setExpectedException('Symfony_Component_Form_Exception_UnexpectedTypeException');

        $this->transformer->transform('1');
    }

    public function testReverseTransformExpectsString()
    {
        $this->setExpectedException('Symfony_Component_Form_Exception_UnexpectedTypeException');

        $this->transformer->reverseTransform(1);
    }

    public function testReverseTransform()
    {
        $this->assertTrue($this->transformer->reverseTransform(self::TRUE_VALUE));
        $this->assertTrue($this->transformer->reverseTransform('foobar'));
        $this->assertTrue($this->transformer->reverseTransform(''));
        $this->assertFalse($this->transformer->reverseTransform(null));
    }
}
