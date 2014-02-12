<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_Token_PreAuthenticatedTokenTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_PreAuthenticatedToken('foo', 'bar', 'key');
        $this->assertFalse($token->isAuthenticated());

        $token = new Symfony_Component_Security_Core_Authentication_Token_PreAuthenticatedToken('foo', 'bar', 'key', array('ROLE_FOO'));
        $this->assertTrue($token->isAuthenticated());
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO')), $token->getRoles());
        $this->assertEquals('key', $token->getProviderKey());
    }

    public function testGetCredentials()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_PreAuthenticatedToken('foo', 'bar', 'key');
        $this->assertEquals('bar', $token->getCredentials());
    }

    public function testGetUser()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_PreAuthenticatedToken('foo', 'bar', 'key');
        $this->assertEquals('foo', $token->getUser());
    }

    public function testEraseCredentials()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_PreAuthenticatedToken('foo', 'bar', 'key');
        $token->eraseCredentials();
        $this->assertEquals('', $token->getCredentials());
    }
}
