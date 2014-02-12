<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Role_SwitchUserRoleTest extends PHPUnit_Framework_TestCase
{
    public function testGetSource()
    {
        $role = new Symfony_Component_Security_Core_Role_SwitchUserRole('FOO', $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));

        $this->assertSame($token, $role->getSource());
    }

    public function testGetRole()
    {
        $role = new Symfony_Component_Security_Core_Role_SwitchUserRole('FOO', $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));

        $this->assertEquals('FOO', $role->getRole());
    }
}
