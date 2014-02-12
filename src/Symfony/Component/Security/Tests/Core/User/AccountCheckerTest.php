<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_User_UserCheckerTest extends PHPUnit_Framework_TestCase
{
    public function testCheckPreAuthNotAdvancedUserInterface()
    {
        $checker = new Symfony_Component_Security_Core_User_UserChecker();

        $this->assertNull($checker->checkPreAuth($this->getMock('Symfony_Component_Security_Core_User_UserInterface')));
    }

    public function testCheckPreAuthPass()
    {
        $checker = new Symfony_Component_Security_Core_User_UserChecker();

        $account = $this->getMock('Symfony_Component_Security_Core_User_AdvancedUserInterface');
        $account->expects($this->once())->method('isCredentialsNonExpired')->will($this->returnValue(true));

        $this->assertNull($checker->checkPreAuth($account));
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_CredentialsExpiredException
     */
    public function testCheckPreAuthCredentialsExpired()
    {
        $checker = new Symfony_Component_Security_Core_User_UserChecker();

        $account = $this->getMock('Symfony_Component_Security_Core_User_AdvancedUserInterface');
        $account->expects($this->once())->method('isCredentialsNonExpired')->will($this->returnValue(false));

        $checker->checkPreAuth($account);
    }

    public function testCheckPostAuthNotAdvancedUserInterface()
    {
        $checker = new Symfony_Component_Security_Core_User_UserChecker();

        $this->assertNull($checker->checkPostAuth($this->getMock('Symfony_Component_Security_Core_User_UserInterface')));
    }

    public function testCheckPostAuthPass()
    {
        $checker = new Symfony_Component_Security_Core_User_UserChecker();

        $account = $this->getMock('Symfony_Component_Security_Core_User_AdvancedUserInterface');
        $account->expects($this->once())->method('isAccountNonLocked')->will($this->returnValue(true));
        $account->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $account->expects($this->once())->method('isAccountNonExpired')->will($this->returnValue(true));

        $this->assertNull($checker->checkPostAuth($account));
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_LockedException
     */
    public function testCheckPostAuthAccountLocked()
    {
        $checker = new Symfony_Component_Security_Core_User_UserChecker();

        $account = $this->getMock('Symfony_Component_Security_Core_User_AdvancedUserInterface');
        $account->expects($this->once())->method('isAccountNonLocked')->will($this->returnValue(false));

        $checker->checkPostAuth($account);
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_DisabledException
     */
    public function testCheckPostAuthDisabled()
    {
        $checker = new Symfony_Component_Security_Core_User_UserChecker();

        $account = $this->getMock('Symfony_Component_Security_Core_User_AdvancedUserInterface');
        $account->expects($this->once())->method('isAccountNonLocked')->will($this->returnValue(true));
        $account->expects($this->once())->method('isEnabled')->will($this->returnValue(false));

        $checker->checkPostAuth($account);
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_AccountExpiredException
     */
    public function testCheckPostAuthAccountExpired()
    {
        $checker = new Symfony_Component_Security_Core_User_UserChecker();

        $account = $this->getMock('Symfony_Component_Security_Core_User_AdvancedUserInterface');
        $account->expects($this->once())->method('isAccountNonLocked')->will($this->returnValue(true));
        $account->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $account->expects($this->once())->method('isAccountNonExpired')->will($this->returnValue(false));

        $checker->checkPostAuth($account);
    }
}
