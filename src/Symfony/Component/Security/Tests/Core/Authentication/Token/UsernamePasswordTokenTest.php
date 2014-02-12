<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_Token_UsernamePasswordTokenTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('foo', 'bar', 'key');
        $this->assertFalse($token->isAuthenticated());

        $token = new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('foo', 'bar', 'key', array('ROLE_FOO'));
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO')), $token->getRoles());
        $this->assertTrue($token->isAuthenticated());
        $this->assertEquals('key', $token->getProviderKey());
    }

    /**
     * @expectedException LogicException
     */
    public function testSetAuthenticatedToTrue()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('foo', 'bar', 'key');
        $token->setAuthenticated(true);
    }

    public function testSetAuthenticatedToFalse()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('foo', 'bar', 'key');
        $token->setAuthenticated(false);
        $this->assertFalse($token->isAuthenticated());
    }

    public function testEraseCredentials()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('foo', 'bar', 'key');
        $token->eraseCredentials();
        $this->assertEquals('', $token->getCredentials());
    }

    public function testToString()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('foo', '', 'foo', array('A', 'B'));
        $this->assertEquals('UsernamePasswordToken(user="foo", authenticated=true, roles="A, B")', (string) $token->__toString());
    }
}
