<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_PercentToLocalizedStringTransformerTest extends Symfony_Component_Form_Tests_Extension_Core_DataTransformer_LocalizedTestCase
{
    protected function setUp()
    {
        parent::setUp();

        Locale::setDefault('en');
    }

    public function testTransform()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_PercentToLocalizedStringTransformer();

        $this->assertEquals('10', $transformer->transform(0.1));
        $this->assertEquals('15', $transformer->transform(0.15));
        $this->assertEquals('12', $transformer->transform(0.1234));
        $this->assertEquals('200', $transformer->transform(2));
    }

    public function testTransformEmpty()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_PercentToLocalizedStringTransformer();

        $this->assertEquals('', $transformer->transform(null));
    }

    public function testTransformWithInteger()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_PercentToLocalizedStringTransformer(null, 'integer');

        $this->assertEquals('0', $transformer->transform(0.1));
        $this->assertEquals('1', $transformer->transform(1));
        $this->assertEquals('15', $transformer->transform(15));
        $this->assertEquals('16', $transformer->transform(15.9));
    }

    public function testTransformWithPrecision()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_PercentToLocalizedStringTransformer(2);

        $this->assertEquals('12.34', $transformer->transform(0.1234));
    }

    public function testReverseTransform()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_PercentToLocalizedStringTransformer();

        $this->assertEquals(0.1, $transformer->reverseTransform('10'));
        $this->assertEquals(0.15, $transformer->reverseTransform('15'));
        $this->assertEquals(0.12, $transformer->reverseTransform('12'));
        $this->assertEquals(2, $transformer->reverseTransform('200'));
    }

    public function testReverseTransformEmpty()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_PercentToLocalizedStringTransformer();

        $this->assertNull($transformer->reverseTransform(''));
    }

    public function testReverseTransformWithInteger()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_PercentToLocalizedStringTransformer(null, 'integer');

        $this->assertEquals(10, $transformer->reverseTransform('10'));
        $this->assertEquals(15, $transformer->reverseTransform('15'));
        $this->assertEquals(12, $transformer->reverseTransform('12'));
        $this->assertEquals(200, $transformer->reverseTransform('200'));
    }

    public function testReverseTransformWithPrecision()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_PercentToLocalizedStringTransformer(2);

        $this->assertEquals(0.1234, $transformer->reverseTransform('12.34'));
    }

    public function testTransformExpectsNumeric()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_PercentToLocalizedStringTransformer();

        $this->setExpectedException('Symfony_Component_Form_Exception_UnexpectedTypeException');

        $transformer->transform('foo');
    }

    public function testReverseTransformExpectsString()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_PercentToLocalizedStringTransformer();

        $this->setExpectedException('Symfony_Component_Form_Exception_UnexpectedTypeException');

        $transformer->reverseTransform(1);
    }
}
