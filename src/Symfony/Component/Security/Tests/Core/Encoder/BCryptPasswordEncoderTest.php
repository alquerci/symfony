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
 * @author Elnur Abdurrakhimov <elnur@elnur.pro>
 */
class Symfony_Component_Security_Tests_Core_Encoder_BCryptPasswordEncoderTest extends PHPUnit_Framework_TestCase
{
    const PASSWORD = 'password';
    const BYTES = '0123456789abcdef';
    const VALID_COST = '04';

    const SECURE_RANDOM_INTERFACE = 'Symfony_Component_Security_Core_Util_SecureRandomInterface';

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $secureRandom;

    protected function setUp()
    {
        if (!function_exists('password_hash') && !CRYPT_BLOWFISH) {
            $this->markTestSkipped('Requires blowfish hash type or PHP 5.5 or install the "ircmaxell/password-compat" via Composer.');
        }

        $this->secureRandom = $this->getMock(self::SECURE_RANDOM_INTERFACE);

        $this->secureRandom
            ->expects($this->any())
            ->method('nextBytes')
            ->will($this->returnValue(self::BYTES))
        ;
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCostBelowRange()
    {
        new Symfony_Component_Security_Core_Encoder_BCryptPasswordEncoder($this->secureRandom, 3);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCostAboveRange()
    {
        new Symfony_Component_Security_Core_Encoder_BCryptPasswordEncoder($this->secureRandom, 32);
    }

    public function testCostInRange()
    {
        for ($cost = 4; $cost <= 31; $cost++) {
            new Symfony_Component_Security_Core_Encoder_BCryptPasswordEncoder($this->secureRandom, $cost);
        }
    }

    public function testResultLength()
    {
        $encoder = new Symfony_Component_Security_Core_Encoder_BCryptPasswordEncoder($this->secureRandom, self::VALID_COST);
        $result = $encoder->encodePassword(self::PASSWORD, null);
        $this->assertEquals(60, strlen($result));
    }

    public function testValidation()
    {
        $encoder = new Symfony_Component_Security_Core_Encoder_BCryptPasswordEncoder($this->secureRandom, self::VALID_COST);
        $result = $encoder->encodePassword(self::PASSWORD, null);
        $this->assertTrue($encoder->isPasswordValid($result, self::PASSWORD, null));
        $this->assertFalse($encoder->isPasswordValid($result, 'anotherPassword', null));
    }

    public function testValidationKnownPassword()
    {
        $encoder = new Symfony_Component_Security_Core_Encoder_BCryptPasswordEncoder($this->secureRandom, self::VALID_COST);
        $prefix = '$'.(version_compare(phpversion(), '5.3.7', '>=')
                       ? '2y' : '2a').'$';

        $encrypted = $prefix.'04$ABCDEFGHIJKLMNOPQRSTU.uTmwd4KMSHxbUsG7bng8x7YdA0PM1iq';
        $this->assertTrue($encoder->isPasswordValid($encrypted, self::PASSWORD, null));
    }

    public function testSecureRandomIsUsed()
    {
        if (function_exists('mcrypt_create_iv')) {
            return;
        }

        $this->secureRandom
            ->expects($this->atLeastOnce())
            ->method('nextBytes')
        ;

        $encoder = new Symfony_Component_Security_Core_Encoder_BCryptPasswordEncoder($this->secureRandom, self::VALID_COST);
        $result = $encoder->encodePassword(self::PASSWORD, null);

        $prefix = '$'.(version_compare(phpversion(), '5.3.7', '>=')
                       ? '2y' : '2a').'$';
        $salt = 'MDEyMzQ1Njc4OWFiY2RlZe';
        $expected = crypt(self::PASSWORD, $prefix . self::VALID_COST . '$' . $salt);

        $this->assertEquals($expected, $result);
    }
}
