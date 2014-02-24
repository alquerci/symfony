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
 * Transforms between a timestamp and a DateTime object
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 */
class Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToTimestampTransformer extends Symfony_Component_Form_Extension_Core_DataTransformer_BaseDateTimeTransformer
{
    /**
     * Transforms a DateTime object into a timestamp in the configured timezone.
     *
     * @param DateTime $value A DateTime object
     *
     * @return integer A timestamp
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value is not an instance of DateTime
     * @throws Symfony_Component_Form_Exception_TransformationFailedException if the output timezone is not supported
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof DateTime) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($value, 'DateTime');
        }

        $value = clone $value;
        try {
            $value->setTimezone(new DateTimeZone($this->outputTimezone));
        } catch (Exception $e) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }

        return (int) $value->format('U');
    }

    /**
     * Transforms a timestamp in the configured timezone into a DateTime object
     *
     * @param string $value A timestamp
     *
     * @return DateTime      An instance of DateTime
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value is not a timestamp
     * @throws Symfony_Component_Form_Exception_TransformationFailedException if the given timestamp is invalid
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_numeric($value)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($value, 'numeric');
        }

        try {
            $dateTime = new DateTime();
            $dateTime->setTimezone(new DateTimeZone($this->outputTimezone));
            $dateTime->setTimestamp($value);

            if ($this->inputTimezone !== $this->outputTimezone) {
                $dateTime->setTimezone(new DateTimeZone($this->inputTimezone));
            }
        } catch (Exception $e) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }

        return $dateTime;
    }
}
