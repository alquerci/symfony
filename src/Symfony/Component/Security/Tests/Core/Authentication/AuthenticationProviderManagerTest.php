<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_AuthenticationProviderManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testAuthenticateWithoutProviders()
    {
        new Symfony_Component_Security_Core_Authentication_AuthenticationProviderManager(array());
    }

    public function testAuthenticateWhenNoProviderSupportsToken()
    {
        $manager = new Symfony_Component_Security_Core_Authentication_AuthenticationProviderManager(array(
            $this->getAuthenticationProvider(false),
        ));

        try {
            $manager->authenticate($token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));
            $this->fail();
        } catch (Symfony_Component_Security_Core_Exception_ProviderNotFoundException $e) {
            $this->assertSame($token, $e->getToken());
        }
    }

    public function testAuthenticateWhenProviderReturnsAccountStatusException()
    {
        $manager = new Symfony_Component_Security_Core_Authentication_AuthenticationProviderManager(array(
            $this->getAuthenticationProvider(true, null, 'Symfony_Component_Security_Core_Exception_AccountStatusException'),
        ));

        try {
            $manager->authenticate($token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));
            $this->fail();
        } catch (Symfony_Component_Security_Core_Exception_AccountStatusException $e) {
            $this->assertSame($token, $e->getToken());
        }
    }

    public function testAuthenticateWhenProviderReturnsAuthenticationException()
    {
        $manager = new Symfony_Component_Security_Core_Authentication_AuthenticationProviderManager(array(
            $this->getAuthenticationProvider(true, null, 'Symfony_Component_Security_Core_Exception_AuthenticationException'),
        ));

        try {
            $manager->authenticate($token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));
            $this->fail();
        } catch (Symfony_Component_Security_Core_Exception_AuthenticationException $e) {
            $this->assertSame($token, $e->getToken());
        }
    }

    public function testAuthenticateWhenOneReturnsAuthenticationExceptionButNotAll()
    {
        $manager = new Symfony_Component_Security_Core_Authentication_AuthenticationProviderManager(array(
            $this->getAuthenticationProvider(true, null, 'Symfony_Component_Security_Core_Exception_AuthenticationException'),
            $this->getAuthenticationProvider(true, $expected = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface')),
        ));

        $token = $manager->authenticate($this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));
        $this->assertSame($expected, $token);
    }

    public function testAuthenticateReturnsTokenOfTheFirstMatchingProvider()
    {
        $second = $this->getMock('Symfony_Component_Security_Core_Authentication_Provider_AuthenticationProviderInterface');
        $second
            ->expects($this->never())
            ->method('supports')
        ;
        $manager = new Symfony_Component_Security_Core_Authentication_AuthenticationProviderManager(array(
            $this->getAuthenticationProvider(true, $expected = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface')),
            $second,
        ));

        $token = $manager->authenticate($this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));
        $this->assertSame($expected, $token);
    }

    public function testEraseCredentialFlag()
    {
        $manager = new Symfony_Component_Security_Core_Authentication_AuthenticationProviderManager(array(
            $this->getAuthenticationProvider(true, $token = new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('foo', 'bar', 'key')),
        ));

        $token = $manager->authenticate($this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));
        $this->assertEquals('', $token->getCredentials());

        $manager = new Symfony_Component_Security_Core_Authentication_AuthenticationProviderManager(array(
            $this->getAuthenticationProvider(true, $token = new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('foo', 'bar', 'key')),
        ), false);

        $token = $manager->authenticate($this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));
        $this->assertEquals('bar', $token->getCredentials());
    }

    protected function getAuthenticationProvider($supports, $token = null, $exception = null)
    {
        $provider = $this->getMock('Symfony_Component_Security_Core_Authentication_Provider_AuthenticationProviderInterface');
        $provider->expects($this->once())
                 ->method('supports')
                 ->will($this->returnValue($supports))
        ;

        if (null !== $token) {
            $provider->expects($this->once())
                     ->method('authenticate')
                     ->will($this->returnValue($token))
            ;
        } elseif (null !== $exception) {
            $provider->expects($this->once())
                     ->method('authenticate')
                     ->will($this->throwException($this->getMock($exception, null, array(), '', false)))
            ;
        }

        return $provider;
    }
}
