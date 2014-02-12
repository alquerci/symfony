<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_Provider_AnonymousAuthenticationProviderTest extends PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $provider = $this->getProvider('foo');

        $this->assertTrue($provider->supports($this->getSupportedToken('foo')));
        $this->assertFalse($provider->supports($this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface')));
    }

    public function testAuthenticateWhenTokenIsNotSupported()
    {
        $provider = $this->getProvider('foo');

        $this->assertNull($provider->authenticate($this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface')));
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_BadCredentialsException
     */
    public function testAuthenticateWhenKeyIsNotValid()
    {
        $provider = $this->getProvider('foo');

        $this->assertNull($provider->authenticate($this->getSupportedToken('bar')));
    }

    public function testAuthenticate()
    {
        $provider = $this->getProvider('foo');
        $token = $this->getSupportedToken('foo');

        $this->assertSame($token, $provider->authenticate($token));
    }

    protected function getSupportedToken($key)
    {
        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_AnonymousToken', array('getKey'), array(), '', false);
        $token->expects($this->any())
              ->method('getKey')
              ->will($this->returnValue($key))
        ;

        return $token;
    }

    protected function getProvider($key)
    {
        return new Symfony_Component_Security_Core_Authentication_Provider_AnonymousAuthenticationProvider($key);
    }
}
