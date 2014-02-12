<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Encoder_PlaintextPasswordEncoderTest extends PHPUnit_Framework_TestCase
{
    public function testIsPasswordValid()
    {
        $encoder = new Symfony_Component_Security_Core_Encoder_PlaintextPasswordEncoder();

        $this->assertTrue($encoder->isPasswordValid('foo', 'foo', ''));
        $this->assertFalse($encoder->isPasswordValid('bar', 'foo', ''));
        $this->assertFalse($encoder->isPasswordValid('FOO', 'foo', ''));

        $encoder = new Symfony_Component_Security_Core_Encoder_PlaintextPasswordEncoder(true);

        $this->assertTrue($encoder->isPasswordValid('foo', 'foo', ''));
        $this->assertFalse($encoder->isPasswordValid('bar', 'foo', ''));
        $this->assertTrue($encoder->isPasswordValid('FOO', 'foo', ''));
    }

    public function testEncodePassword()
    {
        $encoder = new Symfony_Component_Security_Core_Encoder_PlaintextPasswordEncoder();

        $this->assertSame('foo', $encoder->encodePassword('foo', ''));
    }
}
