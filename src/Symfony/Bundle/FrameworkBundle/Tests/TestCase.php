<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_TestCase extends PHPUnit_Framework_TestCase
{
    public static function assertInstanceOf($expected, $actual, $message = '')
    {
        self::assertThat($actual, self::isInstanceOf($expected), $message);
    }

    public static function assertEmpty($actual, $message = '')
    {
        if ($actual instanceof Countable) {
            $state = count($actual) === 0;
        } else {
            $state = empty($actual);
        }

        self::assertTrue($state, $message);
    }

    public static function assertCount($expectedCount, $haystack, $message = '')
    {
        $actual = 0;
        if ($haystack instanceof Countable || is_array($haystack)) {
            $actual = count($haystack);
        } elseif ($haystack instanceof Traversable) {
            $actual = iterator_count($haystack);
        }

        self::assertEquals($expectedCount, $actual, $message);
    }
}
