<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_DateTimeToRfc3339TransformerTest extends Symfony_Component_Form_Tests_Extension_Core_DataTransformer_DateTimeTestCase
{
    protected $dateTime;
    protected $dateTimeWithoutSeconds;

    protected function setUp()
    {
        parent::setUp();

        $this->dateTime = new DateTime('2010-02-03 04:05:06 UTC');
        $this->dateTimeWithoutSeconds = new DateTime('2010-02-03 04:05:00 UTC');
    }

    protected function tearDown()
    {
        $this->dateTime = null;
        $this->dateTimeWithoutSeconds = null;

        parent::tearDown();
    }

    public static function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        if ($expected instanceof DateTime && $actual instanceof DateTime) {
            $expected = $expected->format('c');
            $actual = $actual->format('c');
        }

        parent::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    public function allProvider()
    {
        return array(
            array('UTC', 'UTC', '2010-02-03 04:05:06 UTC', '2010-02-03T04:05:06Z'),
            array('UTC', 'UTC', null, ''),
            array('America/New_York', 'Asia/Hong_Kong', '2010-02-03 04:05:06 America/New_York', '2010-02-03T17:05:06+08:00'),
            array('America/New_York', 'Asia/Hong_Kong', null, ''),
            array('UTC', 'Asia/Hong_Kong', '2010-02-03 04:05:06 UTC', '2010-02-03T12:05:06+08:00'),
            array('America/New_York', 'UTC', '2010-02-03 04:05:06 America/New_York', '2010-02-03T09:05:06Z'),
        );
    }

    public function transformProvider()
    {
        return $this->allProvider();
    }

    public function reverseTransformProvider()
    {
        return array_merge($this->allProvider(), array(
            // format without seconds, as appears in some browsers
            array('UTC', 'UTC', '2010-02-03 04:05:00 UTC', '2010-02-03T04:05Z'),
            array('America/New_York', 'Asia/Hong_Kong', '2010-02-03 04:05:00 America/New_York', '2010-02-03T17:05+08:00'),
        ));
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform($fromTz, $toTz, $from, $to)
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToRfc3339Transformer($fromTz, $toTz);

        $this->assertSame($to, $transformer->transform(null !== $from ? new DateTime($from) : null));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testTransformRequiresValidDateTime()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToRfc3339Transformer();
        $transformer->transform('2010-01-01');
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($toTz, $fromTz, $to, $from)
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToRfc3339Transformer($toTz, $fromTz);

        if (null !== $to) {
            $this->assertDateTimeEquals(new DateTime($to), $transformer->reverseTransform($from));
        } else {
            $this->assertSame($to, $transformer->reverseTransform($from));
        }
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testReverseTransformRequiresString()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToRfc3339Transformer();
        $transformer->reverseTransform(12345);
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformWithNonExistingDate()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToRfc3339Transformer('UTC', 'UTC');

        $transformer->reverseTransform('2010-04-31T04:05Z');
    }
}
