<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_Token_AnonymousTokenTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_AnonymousToken('foo', 'bar');
        $this->assertTrue($token->isAuthenticated());

        $token = new Symfony_Component_Security_Core_Authentication_Token_AnonymousToken('foo', 'bar', array('ROLE_FOO'));
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO')), $token->getRoles());
    }

    public function testGetKey()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_AnonymousToken('foo', 'bar');
        $this->assertEquals('foo', $token->getKey());
    }

    public function testGetCredentials()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_AnonymousToken('foo', 'bar');
        $this->assertEquals('', $token->getCredentials());
    }

    public function testGetUser()
    {
        $token = new Symfony_Component_Security_Core_Authentication_Token_AnonymousToken('foo', 'bar');
        $this->assertEquals('bar', $token->getUser());
    }
}
