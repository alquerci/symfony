<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformerTest extends Symfony_Component_Form_Tests_Extension_Core_DataTransformer_DateTimeTestCase
{
    protected $dateTime;
    protected $dateTimeWithoutSeconds;

    protected function setUp()
    {
        parent::setUp();

        Locale::setDefault('en');

        $this->dateTime = new DateTime('2010-02-03 04:05:06 UTC');
        $this->dateTimeWithoutSeconds = new DateTime('2010-02-03 04:05:00 UTC');
    }

    protected function tearDown()
    {
        $this->dateTime = null;
        $this->dateTimeWithoutSeconds = null;

        parent::tearDown();
    }

    public static function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        if ($expected instanceof DateTime && $actual instanceof DateTime) {
            $expected = $expected->format('c');
            $actual = $actual->format('c');
        }

        parent::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    public function dataProvider()
    {
        return array(
            array(IntlDateFormatter::SHORT, null, null, '2/3/10 4:05 AM', '2010-02-03 04:05:00 UTC'),
            array(IntlDateFormatter::MEDIUM, null, null, 'Feb 3, 2010 4:05 AM', '2010-02-03 04:05:00 UTC'),
            array(IntlDateFormatter::LONG, null, null, 'February 3, 2010 4:05 AM', '2010-02-03 04:05:00 UTC'),
            array(IntlDateFormatter::FULL, null, null, 'Wednesday, February 3, 2010 4:05 AM', '2010-02-03 04:05:00 UTC'),
            array(IntlDateFormatter::SHORT, IntlDateFormatter::NONE, null, '2/3/10', '2010-02-03 00:00:00 UTC'),
            array(IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, null, 'Feb 3, 2010', '2010-02-03 00:00:00 UTC'),
            array(IntlDateFormatter::LONG, IntlDateFormatter::NONE, null, 'February 3, 2010', '2010-02-03 00:00:00 UTC'),
            array(IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, 'Wednesday, February 3, 2010', '2010-02-03 00:00:00 UTC'),
            array(null, IntlDateFormatter::SHORT, null, 'Feb 3, 2010 4:05 AM', '2010-02-03 04:05:00 UTC'),
            array(null, IntlDateFormatter::MEDIUM, null, 'Feb 3, 2010 4:05:06 AM', '2010-02-03 04:05:06 UTC'),
            array(null, IntlDateFormatter::LONG, null,
                'Feb 3, 2010 4:05:06 AM GMT' . ($this->isIntlExtensionLoaded() && $this->isLowerThanIcuVersion('4.8') ? '+00:00' : ''),
                '2010-02-03 04:05:06 UTC'),
            // see below for extra test case for time format FULL
            array(IntlDateFormatter::NONE, IntlDateFormatter::SHORT, null, '4:05 AM', '1970-01-01 04:05:00 UTC'),
            array(IntlDateFormatter::NONE, IntlDateFormatter::MEDIUM, null, '4:05:06 AM', '1970-01-01 04:05:06 UTC'),
            array(IntlDateFormatter::NONE, IntlDateFormatter::LONG, null,
                '4:05:06 AM GMT' . ($this->isIntlExtensionLoaded() && $this->isLowerThanIcuVersion('4.8') ? '+00:00' : ''),
                '1970-01-01 04:05:06 UTC'),
            array(null, null, 'yyyy-MM-dd HH:mm:00', '2010-02-03 04:05:00', '2010-02-03 04:05:00 UTC'),
            array(null, null, 'yyyy-MM-dd HH:mm', '2010-02-03 04:05', '2010-02-03 04:05:00 UTC'),
            array(null, null, 'yyyy-MM-dd HH', '2010-02-03 04', '2010-02-03 04:00:00 UTC'),
            array(null, null, 'yyyy-MM-dd', '2010-02-03', '2010-02-03 00:00:00 UTC'),
            array(null, null, 'yyyy-MM', '2010-02', '2010-02-01 00:00:00 UTC'),
            array(null, null, 'yyyy', '2010', '2010-01-01 00:00:00 UTC'),
            array(null, null, 'dd-MM-yyyy', '03-02-2010', '2010-02-03 00:00:00 UTC'),
            array(null, null, 'HH:mm:ss', '04:05:06', '1970-01-01 04:05:06 UTC'),
            array(null, null, 'HH:mm:00', '04:05:00', '1970-01-01 04:05:00 UTC'),
            array(null, null, 'HH:mm', '04:05', '1970-01-01 04:05:00 UTC'),
            array(null, null, 'HH', '04', '1970-01-01 04:00:00 UTC'),
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testTransform($dateFormat, $timeFormat, $pattern, $output, $input)
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer(
            'UTC',
            'UTC',
            $dateFormat,
            $timeFormat,
            IntlDateFormatter::GREGORIAN,
            $pattern
        );

        $input = new DateTime($input);

        $this->assertEquals($output, $transformer->transform($input));
    }

    public function testTransformFullTime()
    {
        if ($this->isLowerThanIcuVersion('4.0')) {
            $this->markTestSkipped('Please upgrade ICU version to 4.0+');
        }

        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer('UTC', 'UTC', null, IntlDateFormatter::FULL);

        $expected = $this->isLowerThanIcuVersion('4.8') ? 'Feb 3, 2010 4:05:06 AM GMT+00:00' : 'Feb 3, 2010 4:05:06 AM GMT';

        $this->assertEquals($expected, $transformer->transform($this->dateTime));
    }

    public function testTransformToDifferentLocale()
    {
        try {
            Locale::setDefault('de_AT');
        } catch (Exception $e) {
            $this->markTestSkipped('The "intl" extension is not available');
        }

        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer('UTC', 'UTC');

        $this->assertEquals('03.02.2010 04:05', $transformer->transform($this->dateTime));
    }

    public function testTransformEmpty()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer();

        $this->assertSame('', $transformer->transform(null));
    }

    public function testTransformWithDifferentTimezones()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer('America/New_York', 'Asia/Hong_Kong');

        $input = new DateTime('2010-02-03 04:05:06 America/New_York');

        $dateTime = clone $input;
        $dateTime->setTimezone(new DateTimeZone('Asia/Hong_Kong'));

        $this->assertEquals($dateTime->format('M j, Y g:i A'), $transformer->transform($input));
    }

    public function testTransformWithDifferentPatterns()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer('UTC', 'UTC', IntlDateFormatter::FULL, IntlDateFormatter::FULL, IntlDateFormatter::GREGORIAN, 'MM*yyyy*dd HH|mm|ss');

        $this->assertEquals('02*2010*03 04|05|06', $transformer->transform($this->dateTime));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testTransformRequiresValidDateTime()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer();
        $transformer->transform('2010-01-01');
    }

    public function testTransformWrapsIntlErrors()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer();

        // HOW TO REPRODUCE?

        //$this->setExpectedException('Symfony_Component_Form_Extension_Core_DataTransformer_Transdate_formationFailedException');

        //$transformer->transform(1.5);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testReverseTransform($dateFormat, $timeFormat, $pattern, $input, $output)
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer(
            'UTC',
            'UTC',
            $dateFormat,
            $timeFormat,
            IntlDateFormatter::GREGORIAN,
            $pattern
        );

        $output = new DateTime($output);

        $this->assertEquals($output, $transformer->reverseTransform($input));
    }

    public function testReverseTransformFullTime()
    {
        if ($this->isLowerThanIcuVersion('4.0')) {
            $this->markTestSkipped('Please upgrade ICU version to 4.0+');
        }

        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer('UTC', 'UTC', null, IntlDateFormatter::FULL);

        $this->assertDateTimeEquals($this->dateTime, $transformer->reverseTransform('Feb 3, 2010 4:05:06 AM GMT+00:00'));
    }

    public function testReverseTransformFromDifferentLocale()
    {
        try {
            Locale::setDefault('de_AT');
        } catch (Exception $e) {
            $this->markTestSkipped('The "intl" extension is not available');
        }

        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer('UTC', 'UTC');

        $this->assertDateTimeEquals($this->dateTimeWithoutSeconds, $transformer->reverseTransform('03.02.2010 04:05'));
    }

    public function testReverseTransformWithDifferentTimezones()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer('America/New_York', 'Asia/Hong_Kong');

        $dateTime = new DateTime('2010-02-03 04:05:00 Asia/Hong_Kong');
        $dateTime->setTimezone(new DateTimeZone('America/New_York'));

        $this->assertDateTimeEquals($dateTime, $transformer->reverseTransform('Feb 3, 2010 4:05 AM'));
    }

    public function testReverseTransformWithDifferentPatterns()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer('UTC', 'UTC', IntlDateFormatter::FULL, IntlDateFormatter::FULL, IntlDateFormatter::GREGORIAN, 'MM*yyyy*dd HH|mm|ss');

        $this->assertDateTimeEquals($this->dateTime, $transformer->reverseTransform('02*2010*03 04|05|06'));
    }

    public function testReverseTransformEmpty()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer();

        $this->assertNull($transformer->reverseTransform(''));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testReverseTransformRequiresString()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer();
        $transformer->reverseTransform(12345);
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformWrapsIntlErrors()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer();
        $transformer->reverseTransform('12345');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testValidateDateFormatOption()
    {
        new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer(null, null, 'foobar');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testValidateTimeFormatOption()
    {
        new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer(null, null, null, 'foobar');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformWithNonExistingDate()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer('UTC', 'UTC', IntlDateFormatter::SHORT);

        $this->assertDateTimeEquals($this->dateTimeWithoutSeconds, $transformer->reverseTransform('31.04.10 04:05'));
    }
}
