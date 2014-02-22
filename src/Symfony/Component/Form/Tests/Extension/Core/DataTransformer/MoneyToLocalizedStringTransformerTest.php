<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_MoneyToLocalizedStringTransformerTest extends Symfony_Component_Form_Tests_Extension_Core_DataTransformer_LocalizedTestCase
{
    protected function setUp()
    {
        parent::setUp();

        Locale::setDefault('en');
    }

    public function testTransform()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_MoneyToLocalizedStringTransformer(null, null, null, 100);

        $this->assertEquals('1.23', $transformer->transform(123));
    }

    public function testTransformExpectsNumeric()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_MoneyToLocalizedStringTransformer(null, null, null, 100);

        $this->setExpectedException('Symfony_Component_Form_Exception_UnexpectedTypeException');

        $transformer->transform('abcd');
    }

    public function testTransformEmpty()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_MoneyToLocalizedStringTransformer();

        $this->assertSame('', $transformer->transform(null));
    }

    public function testReverseTransform()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_MoneyToLocalizedStringTransformer(null, null, null, 100);

        $this->assertEquals(123, $transformer->reverseTransform('1.23'));
    }

    public function testReverseTransformExpectsString()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_MoneyToLocalizedStringTransformer(null, null, null, 100);

        $this->setExpectedException('Symfony_Component_Form_Exception_UnexpectedTypeException');

        $transformer->reverseTransform(12345);
    }

    public function testReverseTransformEmpty()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_MoneyToLocalizedStringTransformer();

        $this->assertNull($transformer->reverseTransform(''));
    }
}
