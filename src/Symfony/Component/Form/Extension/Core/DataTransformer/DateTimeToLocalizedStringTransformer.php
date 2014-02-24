<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Transforms between a normalized time and a localized time string
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 */
class Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToLocalizedStringTransformer extends Symfony_Component_Form_Extension_Core_DataTransformer_BaseDateTimeTransformer
{
    private $dateFormat;
    private $timeFormat;
    private $pattern;
    private $calendar;

    /**
     * Constructor.
     *
     * @see Symfony_Component_Form_Extension_Core_DataTransformer_BaseDateTimeTransformer::formats for available format options
     *
     * @param string  $inputTimezone  The name of the input timezone
     * @param string  $outputTimezone The name of the output timezone
     * @param integer $dateFormat     The date format
     * @param integer $timeFormat     The time format
     * @param integer $calendar       One of the IntlDateFormatter calendar constants
     * @param string  $pattern        A pattern to pass to IntlDateFormatter
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException If a format is not supported
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if a timezone is not a string
     */
    public function __construct($inputTimezone = null, $outputTimezone = null, $dateFormat = null, $timeFormat = null, $calendar = IntlDateFormatter::GREGORIAN, $pattern = null)
    {
        parent::__construct($inputTimezone, $outputTimezone);

        if (null === $dateFormat) {
            $dateFormat = IntlDateFormatter::MEDIUM;
        }

        if (null === $timeFormat) {
            $timeFormat = IntlDateFormatter::SHORT;
        }

        if (!in_array($dateFormat, self::$formats, true)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($dateFormat, implode('", "', self::$formats));
        }

        if (!in_array($timeFormat, self::$formats, true)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($timeFormat, implode('", "', self::$formats));
        }

        $this->dateFormat = $dateFormat;
        $this->timeFormat = $timeFormat;
        $this->calendar = $calendar;
        $this->pattern = $pattern;
    }

    /**
     * Transforms a normalized date into a localized date string/array.
     *
     * @param DateTime $dateTime Normalized date.
     *
     * @return string|array        Localized date string/array.
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value is not an instance of DateTime
     * @throws Symfony_Component_Form_Exception_TransformationFailedException if the date could not be transformed
     */
    public function transform($dateTime)
    {
        if (null === $dateTime) {
            return '';
        }

        if (!$dateTime instanceof DateTime) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($dateTime, 'DateTime');
        }

        // convert time to UTC before passing it to the formatter
        $dateTime = clone $dateTime;
        if ('UTC' !== $this->inputTimezone) {
            $dateTime->setTimezone(new DateTimeZone('UTC'));
        }

        $value = $this->getIntlDateFormatter()->format((int) $dateTime->format('U'));

        if (intl_get_error_code() != 0) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException(intl_get_error_message());
        }

        return $value;
    }

    /**
     * Transforms a localized date string/array into a normalized date.
     *
     * @param string|array $value Localized date string/array
     *
     * @return DateTime Normalized date
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value is not a string
     * @throws Symfony_Component_Form_Exception_TransformationFailedException if the date could not be parsed
     * @throws Symfony_Component_Form_Exception_TransformationFailedException if the input timezone is not supported
     */
    public function reverseTransform($value)
    {
        if (!is_string($value)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($value, 'string');
        }

        if ('' === $value) {
            return null;
        }

        $timestamp = $this->getIntlDateFormatter()->parse($value);

        if (intl_get_error_code() != 0) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException(intl_get_error_message());
        }

        // read timestamp into DateTime object - the formatter delivers in UTC
        $dateTime = new DateTime(sprintf('@%s UTC', $timestamp));

        if ('UTC' !== $this->inputTimezone) {
            try {
                $dateTime->setTimezone(new DateTimeZone($this->inputTimezone));
            } catch (Exception $e) {
                throw new Symfony_Component_Form_Exception_TransformationFailedException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $dateTime;
    }

    /**
     * Returns a preconfigured IntlDateFormatter instance
     *
     * @return IntlDateFormatter
     */
    protected function getIntlDateFormatter()
    {
        $dateFormat = $this->dateFormat;
        $timeFormat = $this->timeFormat;
        $timezone = $this->outputTimezone;
        $calendar = $this->calendar;
        $pattern = $this->pattern;

        $intlDateFormatter = new IntlDateFormatter(Locale::getDefault(), $dateFormat, $timeFormat, $timezone, $calendar, $pattern);
        $intlDateFormatter->setLenient(false);

        return $intlDateFormatter;
    }
}
