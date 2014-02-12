<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_Provider_RememberMeAuthenticationProviderTest extends PHPUnit_Framework_TestCase
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

        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $this->assertNull($provider->authenticate($token));
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_BadCredentialsException
     */
    public function testAuthenticateWhenKeysDoNotMatch()
    {
        $provider = $this->getProvider(null, 'key1');
        $token = $this->getSupportedToken(null, 'key2');

        $provider->authenticate($token);
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

        $provider->authenticate($this->getSupportedToken());
    }

    public function testAuthenticate()
    {
        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user->expects($this->exactly(2))
             ->method('getRoles')
             ->will($this->returnValue(array('ROLE_FOO')))
        ;

        $provider = $this->getProvider();

        $token = $this->getSupportedToken($user);
        $authToken = $provider->authenticate($token);

        $this->assertInstanceOf('Symfony_Component_Security_Core_Authentication_Token_RememberMeToken', $authToken);
        $this->assertSame($user, $authToken->getUser());
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO')), $authToken->getRoles());
        $this->assertEquals('', $authToken->getCredentials());
    }

    protected function getSupportedToken($user = null, $key = 'test')
    {
        if (null === $user) {
            $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
            $user
                ->expects($this->any())
                ->method('getRoles')
                ->will($this->returnValue(array()))
            ;
        }

        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_RememberMeToken', array('getProviderKey'), array($user, 'foo', $key));
        $token
            ->expects($this->once())
            ->method('getProviderKey')
            ->will($this->returnValue('foo'))
        ;

        return $token;
    }

    protected function getProvider($userChecker = null, $key = 'test')
    {
        if (null === $userChecker) {
            $userChecker = $this->getMock('Symfony_Component_Security_Core_User_UserCheckerInterface');
        }

        return new Symfony_Component_Security_Core_Authentication_Provider_RememberMeAuthenticationProvider($userChecker, $key, 'foo');
    }
}
