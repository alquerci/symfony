<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Util_StringUtilsTest extends PHPUnit_Framework_TestCase
{
    public function testEquals()
    {
        $this->assertTrue(Symfony_Component_Security_Core_Util_StringUtils::equals('password', 'password'));
        $this->assertFalse(Symfony_Component_Security_Core_Util_StringUtils::equals('password', 'foo'));
    }
}
