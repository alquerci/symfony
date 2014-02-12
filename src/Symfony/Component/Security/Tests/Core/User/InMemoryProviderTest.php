<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_User_InMemoryUserProviderTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $provider = new Symfony_Component_Security_Core_User_InMemoryUserProvider(array(
            'fabien' => array(
                'password' => 'foo',
                'enabled'  => false,
                'roles'    => array('ROLE_USER'),
            ),
        ));

        $user = $provider->loadUserByUsername('fabien');
        $this->assertEquals('foo', $user->getPassword());
        $this->assertEquals(array('ROLE_USER'), $user->getRoles());
        $this->assertFalse($user->isEnabled());
    }

    public function testCreateUser()
    {
        $provider = new Symfony_Component_Security_Core_User_InMemoryUserProvider();
        $provider->createUser(new Symfony_Component_Security_Core_User_User('fabien', 'foo'));

        $user = $provider->loadUserByUsername('fabien');
        $this->assertEquals('foo', $user->getPassword());
    }

    /**
     * @expectedException LogicException
     */
    public function testCreateUserAlreadyExist()
    {
        $provider = new Symfony_Component_Security_Core_User_InMemoryUserProvider();
        $provider->createUser(new Symfony_Component_Security_Core_User_User('fabien', 'foo'));
        $provider->createUser(new Symfony_Component_Security_Core_User_User('fabien', 'foo'));
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_UsernameNotFoundException
     */
    public function testLoadUserByUsernameDoesNotExist()
    {
        $provider = new Symfony_Component_Security_Core_User_InMemoryUserProvider();
        $provider->loadUserByUsername('fabien');
    }
}
