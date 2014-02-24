<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_DateTimeToTimestampTransformerTest extends Symfony_Component_Form_Tests_Extension_Core_DataTransformer_DateTimeTestCase
{
    public function testTransform()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToTimestampTransformer('UTC', 'UTC');

        $input = new DateTime('2010-02-03 04:05:06 UTC');
        $output = $input->format('U');

        $this->assertEquals($output, $transformer->transform($input));
    }

    public function testTransformEmpty()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToTimestampTransformer();

        $this->assertNull($transformer->transform(null));
    }

    public function testTransformWithDifferentTimezones()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToTimestampTransformer('Asia/Hong_Kong', 'America/New_York');

        $input = new DateTime('2010-02-03 04:05:06 America/New_York');
        $output = $input->format('U');
        $input->setTimezone(new DateTimeZone('Asia/Hong_Kong'));

        $this->assertEquals($output, $transformer->transform($input));
    }

    public function testTransformFromDifferentTimezone()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToTimestampTransformer('Asia/Hong_Kong', 'UTC');

        $input = new DateTime('2010-02-03 04:05:06 Asia/Hong_Kong');

        $dateTime = clone $input;
        $dateTime->setTimezone(new DateTimeZone('UTC'));
        $output = $dateTime->format('U');

        $this->assertEquals($output, $transformer->transform($input));
    }

    public function testTransformExpectsDateTime()
    {
        $transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToTimestampTransformer();

        $this->setExpectedException('Symfony_Component_Form_Exception_UnexpectedTypeException');

        $transformer->transform('1234');
    }

    public function testReverseTransform()
    {
        $reverseTransformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToTimestampTransformer('UTC', 'UTC');

        $output = new DateTime('2010-02-03 04:05:06 UTC');
        $input = $output->format('U');

        $this->assertDateTimeEquals($output, $reverseTransformer->reverseTransform($input));
    }

    public function testReverseTransformEmpty()
    {
        $reverseTransformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToTimestampTransformer();

        $this->assertNull($reverseTransformer->reverseTransform(null));
    }

    public function testReverseTransformWithDifferentTimezones()
    {
        $reverseTransformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToTimestampTransformer('Asia/Hong_Kong', 'America/New_York');

        $output = new DateTime('2010-02-03 04:05:06 America/New_York');
        $input = $output->format('U');
        $output->setTimezone(new DateTimeZone('Asia/Hong_Kong'));

        $this->assertDateTimeEquals($output, $reverseTransformer->reverseTransform($input));
    }

    public function testReverseTransformExpectsValidTimestamp()
    {
        $reverseTransformer = new Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToTimestampTransformer();

        $this->setExpectedException('Symfony_Component_Form_Exception_UnexpectedTypeException');

        $reverseTransformer->reverseTransform('2010-2010-2010');
    }
}
