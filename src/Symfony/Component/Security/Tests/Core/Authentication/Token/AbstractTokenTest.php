<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_Token_TestUser
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }
}

class Symfony_Component_Security_Tests_Core_Authentication_Token_AbstractTokenTest extends PHPUnit_Framework_TestCase
{
    public function testGetUsername()
    {
        $token = $this->getToken(array('ROLE_FOO'));
        $token->setUser('fabien');
        $this->assertEquals('fabien', $token->getUsername());

        $token->setUser(new Symfony_Component_Security_Tests_Core_Authentication_Token_TestUser('fabien'));
        $this->assertEquals('fabien', $token->getUsername());

        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user->expects($this->once())->method('getUsername')->will($this->returnValue('fabien'));
        $token->setUser($user);
        $this->assertEquals('fabien', $token->getUsername());
    }

    public function testEraseCredentials()
    {
        $token = $this->getToken(array('ROLE_FOO'));

        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user->expects($this->once())->method('eraseCredentials');
        $token->setUser($user);

        $token->eraseCredentials();
    }

    /**
     * @covers Symfony_Component_Security_Core_Authentication_Token_AbstractToken::serialize
     * @covers Symfony_Component_Security_Core_Authentication_Token_AbstractToken::unserialize
     */
    public function testSerialize()
    {
        $token = $this->getToken(array('ROLE_FOO'));
        $token->setAttributes(array('foo' => 'bar'));

        $uToken = unserialize(serialize($token));

        $this->assertEquals($token->getRoles(), $uToken->getRoles());
        $this->assertEquals($token->getAttributes(), $uToken->getAttributes());
    }

    /**
     * @covers Symfony_Component_Security_Core_Authentication_Token_AbstractToken::__construct
     */
    public function testConstructor()
    {
        $token = $this->getToken(array('ROLE_FOO'));
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO')), $token->getRoles());

        $token = $this->getToken(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO')));
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO')), $token->getRoles());

        $token = $this->getToken(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO'), 'ROLE_BAR'));
        $this->assertEquals(array(new Symfony_Component_Security_Core_Role_Role('ROLE_FOO'), new Symfony_Component_Security_Core_Role_Role('ROLE_BAR')), $token->getRoles());
    }

    /**
     * @covers Symfony_Component_Security_Core_Authentication_Token_AbstractToken::isAuthenticated
     * @covers Symfony_Component_Security_Core_Authentication_Token_AbstractToken::setAuthenticated
     */
    public function testAuthenticatedFlag()
    {
        $token = $this->getToken();
        $this->assertFalse($token->isAuthenticated());

        $token->setAuthenticated(true);
        $this->assertTrue($token->isAuthenticated());

        $token->setAuthenticated(false);
        $this->assertFalse($token->isAuthenticated());
    }

    /**
     * @covers Symfony_Component_Security_Core_Authentication_Token_AbstractToken::getAttributes
     * @covers Symfony_Component_Security_Core_Authentication_Token_AbstractToken::setAttributes
     * @covers Symfony_Component_Security_Core_Authentication_Token_AbstractToken::hasAttribute
     * @covers Symfony_Component_Security_Core_Authentication_Token_AbstractToken::getAttribute
     * @covers Symfony_Component_Security_Core_Authentication_Token_AbstractToken::setAttribute
     */
    public function testAttributes()
    {
        $attributes = array('foo' => 'bar');
        $token = $this->getToken();
        $token->setAttributes($attributes);

        $this->assertEquals($attributes, $token->getAttributes(), '->getAttributes() returns the token attributes');
        $this->assertEquals('bar', $token->getAttribute('foo'), '->getAttribute() returns the value of a attribute');
        $token->setAttribute('foo', 'foo');
        $this->assertEquals('foo', $token->getAttribute('foo'), '->setAttribute() changes the value of a attribute');
        $this->assertTrue($token->hasAttribute('foo'), '->hasAttribute() returns true if the attribute is defined');
        $this->assertFalse($token->hasAttribute('oof'), '->hasAttribute() returns false if the attribute is not defined');

        try {
            $token->getAttribute('foobar');
            $this->fail('->getAttribute() throws an InvalidArgumentException exception when the attribute does not exist');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->getAttribute() throws an InvalidArgumentException exception when the attribute does not exist');
            $this->assertEquals('This token has no "foobar" attribute.', $e->getMessage(), '->getAttribute() throws an InvalidArgumentException exception when the attribute does not exist');
        }
    }

    /**
     * @dataProvider getUsers
     */
    public function testSetUser($user)
    {
        $token = $this->getToken();
        $token->setUser($user);
        $this->assertSame($user, $token->getUser());
    }

    public function getUsers()
    {
        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $advancedUser = $this->getMock('Symfony_Component_Security_Core_User_AdvancedUserInterface');

        return array(
            array($advancedUser),
            array($user),
            array(new Symfony_Component_Security_Tests_Core_Authentication_Token_TestUser('foo')),
            array('foo'),
        );
    }

    /**
     * @dataProvider getUserChanges
     */
    public function testSetUserSetsAuthenticatedToFalseWhenUserChanges($firstUser, $secondUser)
    {
        $token = $this->getToken();
        $token->setAuthenticated(true);
        $this->assertTrue($token->isAuthenticated());

        $token->setUser($firstUser);
        $this->assertTrue($token->isAuthenticated());

        $token->setUser($secondUser);
        $this->assertFalse($token->isAuthenticated());
    }

    public function getUserChanges()
    {
        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $advancedUser = $this->getMock('Symfony_Component_Security_Core_User_AdvancedUserInterface');

        return array(
            array(
                'foo', 'bar',
            ),
            array(
                'foo', new Symfony_Component_Security_Tests_Core_Authentication_Token_TestUser('bar'),
            ),
            array(
                'foo', $user,
            ),
            array(
                'foo', $advancedUser
            ),
            array(
                $user, 'foo'
            ),
            array(
                $advancedUser, 'foo'
            ),
            array(
                $user, new Symfony_Component_Security_Tests_Core_Authentication_Token_TestUser('foo'),
            ),
            array(
                $advancedUser, new Symfony_Component_Security_Tests_Core_Authentication_Token_TestUser('foo'),
            ),
            array(
                new Symfony_Component_Security_Tests_Core_Authentication_Token_TestUser('foo'), new Symfony_Component_Security_Tests_Core_Authentication_Token_TestUser('bar'),
            ),
            array(
                new Symfony_Component_Security_Tests_Core_Authentication_Token_TestUser('foo'), 'bar',
            ),
            array(
                new Symfony_Component_Security_Tests_Core_Authentication_Token_TestUser('foo'), $user,
            ),
            array(
                new Symfony_Component_Security_Tests_Core_Authentication_Token_TestUser('foo'), $advancedUser,
            ),
            array(
                $user, $advancedUser
            ),
            array(
                $advancedUser, $user
            ),
        );
    }

    /**
     * @dataProvider getUsers
     */
    public function testSetUserDoesNotSetAuthenticatedToFalseWhenUserDoesNotChange($user)
    {
        $token = $this->getToken();
        $token->setAuthenticated(true);
        $this->assertTrue($token->isAuthenticated());

        $token->setUser($user);
        $this->assertTrue($token->isAuthenticated());

        $token->setUser($user);
        $this->assertTrue($token->isAuthenticated());
    }

    protected function getToken(array $roles = array())
    {
        return $this->getMockForAbstractClass('Symfony_Component_Security_Core_Authentication_Token_AbstractToken', array($roles));
    }
}
