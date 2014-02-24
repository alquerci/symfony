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
 * Transforms between an integer and a localized number with grouping
 * (each thousand) and comma separators.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformer extends Symfony_Component_Form_Extension_Core_DataTransformer_NumberToLocalizedStringTransformer
{
    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        if (!is_string($value)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($value, 'string');
        }

        if ('' === $value) {
            return null;
        }

        if ('NaN' === $value) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException('"NaN" is not a valid integer');
        }

        $formatter = $this->getNumberFormatter();
        $value = $formatter->parse(
            $value,
            PHP_INT_SIZE == 8 ? $formatter::TYPE_INT64 : $formatter::TYPE_INT32
        );

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException($formatter->getErrorMessage());
        }

        return $value;
    }
}
