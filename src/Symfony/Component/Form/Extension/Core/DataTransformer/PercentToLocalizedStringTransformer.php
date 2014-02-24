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
 * Transforms between a normalized format (integer or float) and a percentage value.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 */
class Symfony_Component_Form_Extension_Core_DataTransformer_PercentToLocalizedStringTransformer implements Symfony_Component_Form_DataTransformerInterface
{
    const FRACTIONAL = 'fractional';
    const INTEGER = 'integer';

    protected static $types = array(
        self::FRACTIONAL,
        self::INTEGER,
    );

    private $type;

    private $precision;

    /**
     * Constructor.
     *
     * @see self::$types for a list of supported types
     *
     * @param integer $precision The precision
     * @param string  $type      One of the supported types
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value of type is unknown
     */
    public function __construct($precision = null, $type = null)
    {
        if (null === $precision) {
            $precision = 0;
        }

        if (null === $type) {
            $type = self::FRACTIONAL;
        }

        if (!in_array($type, self::$types, true)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($type, implode('", "', self::$types));
        }

        $this->type = $type;
        $this->precision = $precision;
    }

    /**
     * Transforms between a normalized format (integer or float) into a percentage value.
     *
     * @param number $value Normalized value
     *
     * @return number Percentage value
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value is not numeric
     * @throws Symfony_Component_Form_Exception_TransformationFailedException if the value could not be transformed
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!is_numeric($value)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($value, 'numeric');
        }

        if (self::FRACTIONAL == $this->type) {
            $value *= 100;
        }

        $formatter = $this->getNumberFormatter();
        $value = $formatter->format($value);

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException($formatter->getErrorMessage());
        }

        // replace the UTF-8 non break spaces
        return $value;
    }

    /**
     * Transforms between a percentage value into a normalized format (integer or float).
     *
     * @param number $value Percentage value.
     *
     * @return number Normalized value.
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value is not a string
     * @throws Symfony_Component_Form_Exception_TransformationFailedException if the value could not be transformed
     */
    public function reverseTransform($value)
    {
        if (!is_string($value)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($value, 'string');
        }

        if ('' === $value) {
            return null;
        }

        $formatter = $this->getNumberFormatter();
        // replace normal spaces so that the formatter can read them
        $value = $formatter->parse(str_replace(' ', ' ', $value));

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException($formatter->getErrorMessage());
        }

        if (self::FRACTIONAL == $this->type) {
            $value /= 100;
        }

        return $value;
    }

    /**
     * Returns a preconfigured NumberFormatter instance
     *
     * @return NumberFormatter
     */
    protected function getNumberFormatter()
    {
        $formatter = new NumberFormatter(Locale::getDefault(), NumberFormatter::DECIMAL);

        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $this->precision);

        return $formatter;
    }
}
