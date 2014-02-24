<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_NumberToLocalizedStringTransformerTest extends Symfony_Component_Form_Tests_Extension_Core_DataTransformer_LocalizedTestCase
{
    protected function setUp()
    {
        parent::setUp();

        Locale::setDefault('de_AT');
    }

    public function testTransform()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $this->assertEquals('1', $transformer->transform(1));
        $this->assertEquals('1,5', $transformer->transform(1.5));
        $this->assertEquals('1234,5', $transformer->transform(1234.5));
        $this->assertEquals('12345,912', $transformer->transform(12345.9123));
    }

    public function testTransformEmpty()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $this->assertSame('', $transformer->transform(null));
    }

    public function testTransformWithGrouping()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer(null, true);

        $this->assertEquals('1.234,5', $transformer->transform(1234.5));
        $this->assertEquals('12.345,912', $transformer->transform(12345.9123));
    }

    public function testTransformWithPrecision()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer(2);

        $this->assertEquals('1234,50', $transformer->transform(1234.5));
        $this->assertEquals('678,92', $transformer->transform(678.916));
    }

    public function testTransformWithRoundingMode()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer(null, null, Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer::ROUND_DOWN);
        $this->assertEquals('1234,547', $transformer->transform(1234.547), '->transform() only applies rounding mode if precision set');

        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer(2, null, Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer::ROUND_DOWN);
        $this->assertEquals('1234,54', $transformer->transform(1234.547), '->transform() rounding-mode works');

    }

    public function testReverseTransform()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $this->assertEquals(1, $transformer->reverseTransform('1'));
        $this->assertEquals(1.5, $transformer->reverseTransform('1,5'));
        $this->assertEquals(1234.5, $transformer->reverseTransform('1234,5'));
        $this->assertEquals(12345.912, $transformer->reverseTransform('12345,912'));
    }

    public function testReverseTransformEmpty()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $this->assertNull($transformer->reverseTransform(''));
    }

    public function testReverseTransformWithGrouping()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer(null, true);

        // completely valid format
        $this->assertEquals(1234.5, $transformer->reverseTransform('1.234,5'));
        $this->assertEquals(12345.912, $transformer->reverseTransform('12.345,912'));
        // omit group separator
        $this->assertEquals(1234.5, $transformer->reverseTransform('1234,5'));
        $this->assertEquals(12345.912, $transformer->reverseTransform('12345,912'));
    }

    public function testDecimalSeparatorMayBeDotIfGroupingSeparatorIsNotDot()
    {
        if ($this->isLowerThanIcuVersion('4.7')) {
            $this->markTestSkipped('Please upgrade ICU version to 4.7+');
        }

        Locale::setDefault('fr');
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer(null, true);

        // completely valid format
        $this->assertEquals(1234.5, $transformer->reverseTransform('1 234,5'));
        // accept dots
        $this->assertEquals(1234.5, $transformer->reverseTransform('1 234.5'));
        // omit group separator
        $this->assertEquals(1234.5, $transformer->reverseTransform('1234,5'));
        $this->assertEquals(1234.5, $transformer->reverseTransform('1234.5'));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testDecimalSeparatorMayNotBeDotIfGroupingSeparatorIsDot()
    {
        if ($this->isLowerThanIcuVersion('4.7')) {
            $this->markTestSkipped('Please upgrade ICU version to 4.7+');
        }

        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer(null, true);

        $transformer->reverseTransform('1.234.5');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testDecimalSeparatorMayNotBeDotIfGroupingSeparatorIsDotWithNoGroupSep()
    {
        if ($this->isLowerThanIcuVersion('4.7')) {
            $this->markTestSkipped('Please upgrade ICU version to 4.7+');
        }

        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer(null, true);

        $transformer->reverseTransform('1234.5');
    }

    public function testDecimalSeparatorMayBeDotIfGroupingSeparatorIsDotButNoGroupingUsed()
    {
        Locale::setDefault('fr');
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $this->assertEquals(1234.5, $transformer->reverseTransform('1234,5'));
        $this->assertEquals(1234.5, $transformer->reverseTransform('1234.5'));
    }

    public function testDecimalSeparatorMayBeCommaIfGroupingSeparatorIsNotComma()
    {
        if ($this->isLowerThanIcuVersion('4.7')) {
            $this->markTestSkipped('Please upgrade ICU version to 4.7+');
        }

        Locale::setDefault('ak');
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer(null, true);

        // completely valid format
        $this->assertEquals(1234.5, $transformer->reverseTransform('1 234.5'));
        // accept commas
        $this->assertEquals(1234.5, $transformer->reverseTransform('1 234,5'));
        // omit group separator
        $this->assertEquals(1234.5, $transformer->reverseTransform('1234.5'));
        $this->assertEquals(1234.5, $transformer->reverseTransform('1234,5'));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testDecimalSeparatorMayNotBeCommaIfGroupingSeparatorIsComma()
    {
        if ($this->isLowerThanIcuVersion('4.7')) {
            $this->markTestSkipped('Please upgrade ICU version to 4.7+');
        }

        Locale::setDefault('en');
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer(null, true);

        $transformer->reverseTransform('1,234,5');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testDecimalSeparatorMayNotBeCommaIfGroupingSeparatorIsCommaWithNoGroupSep()
    {
        if ($this->isLowerThanIcuVersion('4.7')) {
            $this->markTestSkipped('Please upgrade ICU version to 4.7+');
        }

        Locale::setDefault('en');
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer(null, true);

        $transformer->reverseTransform('1234,5');
    }

    public function testDecimalSeparatorMayBeCommaIfGroupingSeparatorIsCommaButNoGroupingUsed()
    {
        Locale::setDefault('en');
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $this->assertEquals(1234.5, $transformer->reverseTransform('1234,5'));
        $this->assertEquals(1234.5, $transformer->reverseTransform('1234.5'));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testTransformExpectsNumeric()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $transformer->transform('foo');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testReverseTransformExpectsString()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $transformer->reverseTransform(1);
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformExpectsValidNumber()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $transformer->reverseTransform('foo');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     * @link https://github.com/symfony/symfony/issues/3161
     */
    public function testReverseTransformDisallowsNaN()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $transformer->reverseTransform('NaN');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDisallowsNaN2()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $transformer->reverseTransform('nan');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDisallowsInfinity()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $transformer->reverseTransform('∞');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDisallowsInfinity2()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $transformer->reverseTransform('∞,123');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDisallowsNegativeInfinity()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $transformer->reverseTransform('-∞');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDisallowsLeadingExtraCharacters()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $transformer->reverseTransform('foo123');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDisallowsCenteredExtraCharacters()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $transformer->reverseTransform('12foo3');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDisallowsTrailingExtraCharacters()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer();

        $transformer->reverseTransform('123foo');
    }
}
