<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_Provider_UserAuthenticationProviderTest extends PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $provider = $this->getProvider();

        $this->assertTrue($provider->supports($this->getSupportedToken()));
        $this->assertFalse($provider->supports($this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface')));
    }

    public function testAuthenticateWhenTokenIsNotSupported()
    {
        $provider = $this->getProvider();

        $this->assertNull($provider->authenticate($this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface')));
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_UsernameNotFoundException
     */
    public function testAuthenticateWhenUsernameIsNotFound()
    {
        $provider = $this->getProvider(false, false);
        $provider->expects($this->once())
                 ->method('retrieveUser')
                 ->will($this->throwException($this->getMock('Symfony_Component_Security_Core_Exception_UsernameNotFoundException', null, array(), '', false)))
        ;

        $provider->authenticate($this->getSupportedToken());
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_BadCredentialsException
     */
    public function testAuthenticateWhenUsernameIsNotFoundAndHideIsTrue()
    {
        $provider = $this->getProvider(false, true);
        $provider->expects($this->once())
                 ->method('retrieveUser')
                 ->will($this->throwException($this->getMock('Symfony_Component_Security_Core_Exception_UsernameNotFoundException', null, array(), '', false)))
        ;

        $provider->authenticate($this->getSupportedToken());
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_AuthenticationServiceException
     */
    public function testAuthenticateWhenProviderDoesNotReturnAnUserInterface()
    {
        $provider = $this->getProvider(false, true);
        $provider->expects($this->once())
                 ->method('retrieveUser')
                 ->will($this->returnValue(null))
        ;

        $provider->authenticate($this->getSupportedToken());
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_CredentialsExpiredException
     */
    public function testAuthenticateWhenPreChecksFails()
    {
        $userChecker = $this->getMock('Symfony_Component_Security_Core_User_UserCheckerInterface');
        $userChecker->expects($this->once())
                    ->method('checkPreAuth')
                    ->will($this->throwException($this->getMock('Symfony_Component_Security_Core_Exception_CredentialsExpiredException', null, array(), '', false)))
        ;

        $provider = $this->getProvider($userChecker);
        $provider->expects($this->once())
                 ->method('retrieveUser')
                 ->will($this->returnValue($this->getMock('Symfony_Component_Security_Core_User_UserInterface')))
        ;

        $provider->authenticate($this->getSupportedToken());
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_AccountExpiredException
     */
    public function testAuthenticateWhenPostChecksFails()
    {
        $userChecker = $this->getMock('Symfony_Component_Security_Core_User_UserCheckerInterface');
        $userChecker->expects($this->once())
                    ->method('checkPostAuth')
                    ->will($this->throwException($this->getMock('Symfony_Component_Security_Core_Exception_AccountExpiredException', null, array(), '', false)))
        ;

        $provider = $this->getProvider($userChecker);
        $provider->expects($this->once())
                 ->method('retrieveUser')
                 ->will($this->returnValue($this->getMock('Symfony_Component_Security_Core_User_UserInterface')))
        ;

        $provider->authenticate($this->getSupportedToken());
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_BadCredentialsException
     * @expectedExceptionMessage Bad credentials
     */
    public function testAuthenticateWhenPostCheckAuthenticationFails()
    {
        $provider = $this->getProvider();
        $provider->expects($this->once())
                 ->method('retrieveUser')
                 ->will($this->returnValue($this->getMock('Symfony_Component_Security_Core_User_UserInterface')))
        ;
        $provider->expects($this->once())
                 ->method('checkAuthentication')
                 ->will($this->throwException($this->getMock('Symfony_Component_Security_Core_Exception_BadCredentialsException', null, array(), '', false)))
        ;

        $provider->authenticate($this->getSupportedToken());
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_BadCredentialsException
     * @expectedExceptionMessage Foo
     */
    public function testAuthenticateWhenPostCheckAuthenticationFailsWithHideFalse()
    {
        $provider = $this->getProvider(false, false);
        $provider->expects($this->once())
                 ->method('retrieveUser')
                 ->will($this->returnValue($this->getMock('Symfony_Component_Security_Core_User_UserInterface')))
        ;
        $provider->expects($this->once())
                 ->method('checkAuthentication')
                 ->will($this->throwException(new Symfony_Component_Security_Core_Exception_BadCredentialsException('Foo')))
        ;

        $provider->authenticate($this->getSupportedToken());
    }

    public function testAuthenticate()
    {
        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user->expects($this->once())
             ->method('getRoles')
             ->will($this->returnValue(array('ROLE_FOO')))
        ;

        $provider = $this->getProvider();
        $provider->expects($this->once())
                 ->method('retrieveUser')
                 ->will($this->returnValue($user))
        ;

        $token = $this->getSupportedToken();
        $token->expects($this->once())
              ->method('getCredentials')
              ->will($this->returnValue('foo'))
        ;

        $authToken = $provider->authenticate($token);

        $this->assertInstanceOf('Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken', $authToken);
        $this->assertSame($user, $authToken->getUser());
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO')), $authToken->getRoles());
        $this->assertEquals('foo', $authToken->getCredentials());
        $this->assertEquals(array('foo' => 'bar'), $authToken->getAttributes(), '->authenticate() copies token attributes');
    }

    protected function getSupportedToken()
    {
        $mock = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken', array('getCredentials', 'getProviderKey'), array(), '', false);
        $mock
            ->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue('key'))
        ;

        $mock->setAttributes(array('foo' => 'bar'));

        return $mock;
    }

    protected function getProvider($userChecker = false, $hide = true)
    {
        if (false === $userChecker) {
            $userChecker = $this->getMock('Symfony_Component_Security_Core_User_UserCheckerInterface');
        }

        return $this->getMockForAbstractClass('Symfony_Component_Security_Core_Authentication_Provider_UserAuthenticationProvider', array($userChecker, 'key', $hide));
    }
}
