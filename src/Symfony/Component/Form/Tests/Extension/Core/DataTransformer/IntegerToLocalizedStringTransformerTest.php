<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformerTest extends Symfony_Component_Form_Tests_Extension_Core_DataTransformer_LocalizedTestCase
{
    protected function setUp()
    {
        parent::setUp();

        Locale::setDefault('de_AT');
    }

    public function testReverseTransform()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformer();

        $this->assertEquals(1, $transformer->reverseTransform('1'));
        $this->assertEquals(1, $transformer->reverseTransform('1,5'));
        $this->assertEquals(1234, $transformer->reverseTransform('1234,5'));
        $this->assertEquals(12345, $transformer->reverseTransform('12345,912'));
    }

    public function testReverseTransformEmpty()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformer();

        $this->assertNull($transformer->reverseTransform(''));
    }

    public function testReverseTransformWithGrouping()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformer(null, true);

        $this->assertEquals(1234, $transformer->reverseTransform('1.234,5'));
        $this->assertEquals(12345, $transformer->reverseTransform('12.345,912'));
        $this->assertEquals(1234, $transformer->reverseTransform('1234,5'));
        $this->assertEquals(12345, $transformer->reverseTransform('12345,912'));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testReverseTransformExpectsString()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformer();

        $transformer->reverseTransform(1);
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformExpectsValidNumber()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformer();

        $transformer->reverseTransform('foo');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDisallowsNaN()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformer();

        $transformer->reverseTransform('NaN');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDisallowsNaN2()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformer();

        $transformer->reverseTransform('nan');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDisallowsInfinity()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformer();

        $transformer->reverseTransform('∞');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDisallowsNegativeInfinity()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformer();

        $transformer->reverseTransform('-∞');
    }
}
