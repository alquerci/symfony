<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_User_UserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Symfony_Component_Security_Core_User_User::__construct
     * @expectedException InvalidArgumentException
     */
    public function testConstructorException()
    {
        new Symfony_Component_Security_Core_User_User('', 'superpass');
    }

    /**
     * @covers Symfony_Component_Security_Core_User_User::__construct
     * @covers Symfony_Component_Security_Core_User_User::getRoles
     */
    public function testGetRoles()
    {
        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass');
        $this->assertEquals(array(), $user->getRoles());

        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass', array('ROLE_ADMIN'));
        $this->assertEquals(array('ROLE_ADMIN'), $user->getRoles());
    }

    /**
     * @covers Symfony_Component_Security_Core_User_User::__construct
     * @covers Symfony_Component_Security_Core_User_User::getPassword
     */
    public function testGetPassword()
    {
        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass');
        $this->assertEquals('superpass', $user->getPassword());
    }

    /**
     * @covers Symfony_Component_Security_Core_User_User::__construct
     * @covers Symfony_Component_Security_Core_User_User::getUsername
     */
    public function testGetUsername()
    {
        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass');
        $this->assertEquals('fabien', $user->getUsername());
    }

    /**
     * @covers Symfony_Component_Security_Core_User_User::getSalt
     */
    public function testGetSalt()
    {
        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass');
        $this->assertEquals('', $user->getSalt());
    }

    /**
     * @covers Symfony_Component_Security_Core_User_User::isAccountNonExpired
     */
    public function testIsAccountNonExpired()
    {
        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass');
        $this->assertTrue($user->isAccountNonExpired());

        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass', array(), true, false);
        $this->assertFalse($user->isAccountNonExpired());
    }

    /**
     * @covers Symfony_Component_Security_Core_User_User::isCredentialsNonExpired
     */
    public function testIsCredentialsNonExpired()
    {
        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass');
        $this->assertTrue($user->isCredentialsNonExpired());

        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass', array(), true, true, false);
        $this->assertFalse($user->isCredentialsNonExpired());
    }

    /**
     * @covers Symfony_Component_Security_Core_User_User::isAccountNonLocked
     */
    public function testIsAccountNonLocked()
    {
        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass');
        $this->assertTrue($user->isAccountNonLocked());

        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass', array(), true, true, true, false);
        $this->assertFalse($user->isAccountNonLocked());
    }

    /**
     * @covers Symfony_Component_Security_Core_User_User::isEnabled
     */
    public function testIsEnabled()
    {
        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass');
        $this->assertTrue($user->isEnabled());

        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass', array(), false);
        $this->assertFalse($user->isEnabled());
    }

    /**
     * @covers Symfony_Component_Security_Core_User_User::eraseCredentials
     */
    public function testEraseCredentials()
    {
        $user = new Symfony_Component_Security_Core_User_User('fabien', 'superpass');
        $user->eraseCredentials();
        $this->assertEquals('superpass', $user->getPassword());
    }
}
