<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Encoder_EncoderFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testGetEncoderWithMessageDigestEncoder()
    {
        $factory = new Symfony_Component_Security_Core_Encoder_EncoderFactory(array('Symfony_Component_Security_Core_User_UserInterface' => array(
            'class' => 'Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder',
            'arguments' => array('sha512', true, 5),
        )));

        $encoder = $factory->getEncoder($this->getMock('Symfony_Component_Security_Core_User_UserInterface'));
        $expectedEncoder = new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha512', true, 5);

        $this->assertEquals($expectedEncoder->encodePassword('foo', 'moo'), $encoder->encodePassword('foo', 'moo'));
    }

    public function testGetEncoderWithService()
    {
        $factory = new Symfony_Component_Security_Core_Encoder_EncoderFactory(array(
            'Symfony_Component_Security_Core_User_UserInterface' => new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha1'),
        ));

        $encoder = $factory->getEncoder($this->getMock('Symfony_Component_Security_Core_User_UserInterface'));
        $expectedEncoder = new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha1');
        $this->assertEquals($expectedEncoder->encodePassword('foo', ''), $encoder->encodePassword('foo', ''));

        $encoder = $factory->getEncoder(new Symfony_Component_Security_Core_User_User('user', 'pass'));
        $expectedEncoder = new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha1');
        $this->assertEquals($expectedEncoder->encodePassword('foo', ''), $encoder->encodePassword('foo', ''));
    }

    public function testGetEncoderWithClassName()
    {
        $factory = new Symfony_Component_Security_Core_Encoder_EncoderFactory(array(
            'Symfony_Component_Security_Core_User_UserInterface' => new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha1'),
        ));

        $encoder = $factory->getEncoder('Symfony_Component_Security_Tests_Core_Encoder_SomeChildUser');
        $expectedEncoder = new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha1');
        $this->assertEquals($expectedEncoder->encodePassword('foo', ''), $encoder->encodePassword('foo', ''));
    }

    public function testGetEncoderConfiguredForConcreteClassWithService()
    {
        $factory = new Symfony_Component_Security_Core_Encoder_EncoderFactory(array(
            'Symfony_Component_Security_Core_User_User' => new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha1'),
        ));

        $encoder = $factory->getEncoder(new Symfony_Component_Security_Core_User_User('user', 'pass'));
        $expectedEncoder = new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha1');
        $this->assertEquals($expectedEncoder->encodePassword('foo', ''), $encoder->encodePassword('foo', ''));
    }

    public function testGetEncoderConfiguredForConcreteClassWithClassName()
    {
        $factory = new Symfony_Component_Security_Core_Encoder_EncoderFactory(array(
            'Symfony_Component_Security_Tests_Core_Encoder_SomeUser' => new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha1'),
        ));

        $encoder = $factory->getEncoder('Symfony_Component_Security_Tests_Core_Encoder_SomeChildUser');
        $expectedEncoder = new Symfony_Component_Security_Core_Encoder_MessageDigestPasswordEncoder('sha1');
        $this->assertEquals($expectedEncoder->encodePassword('foo', ''), $encoder->encodePassword('foo', ''));
    }
}

class Symfony_Component_Security_Tests_Core_Encoder_SomeUser implements Symfony_Component_Security_Core_User_UserInterface
{
    public function getRoles() {}
    public function getPassword() {}
    public function getSalt() {}
    public function getUsername() {}
    public function eraseCredentials() {}
}

class Symfony_Component_Security_Tests_Core_Encoder_SomeChildUser extends Symfony_Component_Security_Tests_Core_Encoder_SomeUser
{
}
