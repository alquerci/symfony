<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Symfony_Component_Form_Extension_Core_DataTransformer_BaseDateTimeTransformer implements Symfony_Component_Form_DataTransformerInterface
{
    protected static $formats = array(
        IntlDateFormatter::NONE,
        IntlDateFormatter::FULL,
        IntlDateFormatter::LONG,
        IntlDateFormatter::MEDIUM,
        IntlDateFormatter::SHORT,
    );

    protected $inputTimezone;

    protected $outputTimezone;

    /**
     * Constructor.
     *
     * @param string $inputTimezone  The name of the input timezone
     * @param string $outputTimezone The name of the output timezone
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if a timezone is not a string
     */
    public function __construct($inputTimezone = null, $outputTimezone = null)
    {
        if (!is_string($inputTimezone) && null !== $inputTimezone) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($inputTimezone, 'string');
        }

        if (!is_string($outputTimezone) && null !== $outputTimezone) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($outputTimezone, 'string');
        }

        $this->inputTimezone = $inputTimezone ? $inputTimezone : date_default_timezone_get();
        $this->outputTimezone = $outputTimezone ? $outputTimezone : date_default_timezone_get();
    }
}
