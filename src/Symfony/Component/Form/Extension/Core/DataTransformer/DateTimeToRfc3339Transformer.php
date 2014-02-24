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
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_Core_DataTransformer_DateTimeToRfc3339Transformer extends Symfony_Component_Form_Extension_Core_DataTransformer_BaseDateTimeTransformer
{
    /**
     * {@inheritDoc}
     */
    public function transform($dateTime)
    {
        if (null === $dateTime) {
            return '';
        }

        if (!$dateTime instanceof DateTime) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($dateTime, 'DateTime');
        }

        if ($this->inputTimezone !== $this->outputTimezone) {
            $dateTime = clone $dateTime;
            $dateTime->setTimezone(new DateTimeZone($this->outputTimezone));
        }

        return preg_replace('/\+00:00$/', 'Z', $dateTime->format('c'));
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($rfc3339)
    {
        if (!is_string($rfc3339)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($rfc3339, 'string');
        }

        if ('' === $rfc3339) {
            return null;
        }

        $dateTime = new DateTime($rfc3339);

        if ($this->outputTimezone !== $this->inputTimezone) {
            try {
                $dateTime->setTimezone(new DateTimeZone($this->inputTimezone));
            } catch (Exception $e) {
                throw new Symfony_Component_Form_Exception_TransformationFailedException($e->getMessage(), $e->getCode(), $e);
            }
        }

        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $rfc3339, $matches)) {
            if (!checkdate($matches[2], $matches[3], $matches[1])) {
                throw new Symfony_Component_Form_Exception_TransformationFailedException(sprintf(
                    'The date "%s-%s-%s" is not a valid date.',
                    $matches[1],
                    $matches[2],
                    $matches[3]
                ));
            }
        }

        return $dateTime;
    }
}
