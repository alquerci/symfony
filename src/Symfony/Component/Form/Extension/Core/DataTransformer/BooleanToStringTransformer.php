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
 * Transforms between a Boolean and a string.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 */
class Symfony_Component_Form_Extension_Core_DataTransformer_BooleanToStringTransformer implements Symfony_Component_Form_DataTransformerInterface
{
    /**
     * The value emitted upon transform if the input is true
     * @var string
     */
    private $trueValue;

    /**
     * Sets the value emitted upon transform if the input is true.
     *
     * @param string $trueValue
     */
    public function __construct($trueValue)
    {
        $this->trueValue = $trueValue;
    }

    /**
     * Transforms a Boolean into a string.
     *
     * @param Boolean $value Boolean value.
     *
     * @return string String value.
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value is not a Boolean
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_bool($value)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($value, 'Boolean');
        }

        return true === $value ? $this->trueValue : null;
    }

    /**
     * Transforms a string into a Boolean.
     *
     * @param string $value String value.
     *
     * @return Boolean Boolean value.
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value is not a string
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return false;
        }

        if (!is_string($value)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($value, 'string');
        }

        return true;
    }

}
