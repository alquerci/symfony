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

        $position = 0;
        $formatter = $this->getNumberFormatter();
        $result = $formatter->parse(
            $value,
            PHP_INT_SIZE == 8 ? constant(get_class($formatter).'::TYPE_INT64') : constant(get_class($formatter).'::TYPE_INT32'),
            $position
        );

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException($formatter->getErrorMessage());
        }

        // After parsing, position holds the index of the character where the
        // parsing stopped
        if ($position < strlen($value)) {
            // Check if there are unrecognized characters at the end of the
            // number
            $remainder = substr($value, $position);

            // Remove all whitespace characters
            if ('' !== preg_replace('/[\s\xc2\xa0]*/', '', $remainder)) {
                throw new Symfony_Component_Form_Exception_TransformationFailedException(
                    sprintf('The number contains unrecognized characters: "%s"',
                        $remainder
                    ));
            }
        }

        return $result;
    }
}
