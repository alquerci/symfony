<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Encoder_MessageDigestPasswordEncoderTest extends PHPUnit_Framework_TestCase
{
    public function testIsPasswordValid()
    {
        $encoder = new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha256', false, 1);

        $this->assertTrue($encoder->isPasswordValid(hash('sha256', 'password'), 'password', ''));
    }

    public function testEncodePassword()
    {
        $encoder = new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha256', false, 1);
        $this->assertSame(hash('sha256', 'password'), $encoder->encodePassword('password', ''));

        $encoder = new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha256', true, 1);
        $this->assertSame(base64_encode(hash('sha256', 'password', true)), $encoder->encodePassword('password', ''));

        $encoder = new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha256', false, 2);
        $this->assertSame(hash('sha256', hash('sha256', 'password', true).'password'), $encoder->encodePassword('password', ''));
    }

    /**
     * @expectedException LogicException
     */
    public function testEncodePasswordAlgorithmDoesNotExist()
    {
        $encoder = new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('foobar');
        $encoder->encodePassword('password', '');
    }
}
