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
 * Parser and formatter for AM/PM markers format
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class Symfony_Component_Locale_Stub_DateFormat_AmPmTransformer extends Symfony_Component_Locale_Stub_DateFormat_Transformer
{
    /**
     * {@inheritDoc}
     */
    public function format(DateTime $dateTime, $length)
    {
        return $dateTime->format('A');
    }

    /**
     * {@inheritDoc}
     */
    public function getReverseMatchingRegExp($length)
    {
        return 'AM|PM';
    }

    /**
     * {@inheritDoc}
     */
    public function extractDateOptions($matched, $length)
    {
        return array(
            'marker' => $matched
        );
    }
}
