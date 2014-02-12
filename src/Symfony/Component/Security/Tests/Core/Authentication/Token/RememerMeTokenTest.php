<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_Token_RememberMeTokenTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $user = $this->getUser();
        $token = new Symfony_Component_Security_Core_Authentication_Token_RememberMeToken($user, 'fookey', 'foo');

        $this->assertEquals('fookey', $token->getProviderKey());
        $this->assertEquals('foo', $token->getKey());
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO')), $token->getRoles());
        $this->assertSame($user, $token->getUser());
        $this->assertTrue($token->isAuthenticated());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorKeyCannotBeNull()
    {
        new Symfony_Component_Security_Core_Authentication_Token_RememberMeToken(
            $this->getUser(),
            null,
            null
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorKeyCannotBeEmptyString()
    {
        new Symfony_Component_Security_Core_Authentication_Token_RememberMeToken(
            $this->getUser(),
            '',
            ''
        );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @dataProvider getUserArguments
     * @requires PHP 5.2.0
     */
    public function testConstructorUserCannotBeNull($user)
    {
        new Symfony_Component_Security_Core_Authentication_Token_RememberMeToken($user, 'foo', 'foo');
    }

    public function getUserArguments()
    {
        return array(
            array(null),
            array('foo'),
        );
    }

    protected function getUser($roles = array('ROLE_FOO'))
    {
        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user
            ->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue($roles))
        ;

        return $user;
    }
}
