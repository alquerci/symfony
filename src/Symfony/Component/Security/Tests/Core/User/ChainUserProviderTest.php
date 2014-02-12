<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_User_ChainUserProviderTest extends PHPUnit_Framework_TestCase
{
    public function testLoadUserByUsername()
    {
        $provider1 = $this->getProvider();
        $provider1
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo('foo'))
            ->will($this->throwException(new Symfony_Component_Security_Core_Exception_UsernameNotFoundException('not found')))
        ;

        $provider2 = $this->getProvider();
        $provider2
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue($account = $this->getAccount()))
        ;

        $provider = new Symfony_Component_Security_Core_User_ChainUserProvider(array($provider1, $provider2));
        $this->assertSame($account, $provider->loadUserByUsername('foo'));
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_UsernameNotFoundException
     */
    public function testLoadUserByUsernameThrowsUsernameNotFoundException()
    {
        $provider1 = $this->getProvider();
        $provider1
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo('foo'))
            ->will($this->throwException(new Symfony_Component_Security_Core_Exception_UsernameNotFoundException('not found')))
        ;

        $provider2 = $this->getProvider();
        $provider2
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo('foo'))
            ->will($this->throwException(new Symfony_Component_Security_Core_Exception_UsernameNotFoundException('not found')))
        ;

        $provider = new Symfony_Component_Security_Core_User_ChainUserProvider(array($provider1, $provider2));
        $provider->loadUserByUsername('foo');
    }

    public function testRefreshUser()
    {
        $provider1 = $this->getProvider();
        $provider1
            ->expects($this->once())
            ->method('refreshUser')
            ->will($this->throwException(new Symfony_Component_Security_Core_Exception_UnsupportedUserException('unsupported')))
        ;

        $provider2 = $this->getProvider();
        $provider2
            ->expects($this->once())
            ->method('refreshUser')
            ->will($this->returnValue($account = $this->getAccount()))
        ;

        $provider = new Symfony_Component_Security_Core_User_ChainUserProvider(array($provider1, $provider2));
        $this->assertSame($account, $provider->refreshUser($this->getAccount()));
    }

    public function testRefreshUserAgain()
    {
        $provider1 = $this->getProvider();
        $provider1
            ->expects($this->once())
            ->method('refreshUser')
            ->will($this->throwException(new Symfony_Component_Security_Core_Exception_UsernameNotFoundException('not found')))
        ;

        $provider2 = $this->getProvider();
        $provider2
            ->expects($this->once())
            ->method('refreshUser')
            ->will($this->returnValue($account = $this->getAccount()))
        ;

        $provider = new Symfony_Component_Security_Core_User_ChainUserProvider(array($provider1, $provider2));
        $this->assertSame($account, $provider->refreshUser($this->getAccount()));
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_UnsupportedUserException
     */
    public function testRefreshUserThrowsUnsupportedUserException()
    {
        $provider1 = $this->getProvider();
        $provider1
            ->expects($this->once())
            ->method('refreshUser')
            ->will($this->throwException(new Symfony_Component_Security_Core_Exception_UnsupportedUserException('unsupported')))
        ;

        $provider2 = $this->getProvider();
        $provider2
            ->expects($this->once())
            ->method('refreshUser')
            ->will($this->throwException(new Symfony_Component_Security_Core_Exception_UnsupportedUserException('unsupported')))
        ;

        $provider = new Symfony_Component_Security_Core_User_ChainUserProvider(array($provider1, $provider2));
        $provider->refreshUser($this->getAccount());
    }

    public function testSupportsClass()
    {
        $provider1 = $this->getProvider();
        $provider1
            ->expects($this->once())
            ->method('supportsClass')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(false))
        ;

        $provider2 = $this->getProvider();
        $provider2
            ->expects($this->once())
            ->method('supportsClass')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true))
        ;

        $provider = new Symfony_Component_Security_Core_User_ChainUserProvider(array($provider1, $provider2));
        $this->assertTrue($provider->supportsClass('foo'));
    }

    public function testSupportsClassWhenNotSupported()
    {
        $provider1 = $this->getProvider();
        $provider1
            ->expects($this->once())
            ->method('supportsClass')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(false))
        ;

        $provider2 = $this->getProvider();
        $provider2
            ->expects($this->once())
            ->method('supportsClass')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(false))
        ;

        $provider = new Symfony_Component_Security_Core_User_ChainUserProvider(array($provider1, $provider2));
        $this->assertFalse($provider->supportsClass('foo'));
    }

    protected function getAccount()
    {
        return $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
    }

    protected function getProvider()
    {
        return $this->getMock('Symfony_Component_Security_Core_User_UserProviderInterface');
    }
}
