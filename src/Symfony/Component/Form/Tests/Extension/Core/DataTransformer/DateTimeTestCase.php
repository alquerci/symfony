<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_DateTimeTestCase extends Symfony_Component_Form_Tests_Extension_Core_DataTransformer_LocalizedTestCase
{
    public static function assertDateTimeEquals(DateTime $expected, DateTime $actual)
    {
        self::assertEquals($expected->format('c'), $actual->format('c'));
    }
}
