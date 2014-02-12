<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_SecurityContextTest extends PHPUnit_Framework_TestCase
{
    public function testVoteAuthenticatesTokenIfNecessary()
    {
        $authManager = $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface');
        $decisionManager = $this->getMock('Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface');

        $context = new Symfony_Component_Security_Core_SecurityContext($authManager, $decisionManager);
        $context->setToken($token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));

        $authManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->equalTo($token))
            ->will($this->returnValue($newToken = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface')))
        ;

        $decisionManager
            ->expects($this->once())
            ->method('decide')
            ->will($this->returnValue(true))
        ;

        $this->assertTrue($context->isGranted('foo'));
        $this->assertSame($newToken, $context->getToken());
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_AuthenticationCredentialsNotFoundException
     */
    public function testVoteWithoutAuthenticationToken()
    {
        $context = new Symfony_Component_Security_Core_SecurityContext(
            $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface'),
            $this->getMock('Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface')
        );

        $context->isGranted('ROLE_FOO');
    }

    public function testIsGranted()
    {
        $manager = $this->getMock('Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface');
        $manager->expects($this->once())->method('decide')->will($this->returnValue(false));
        $context = new Symfony_Component_Security_Core_SecurityContext($this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface'), $manager);
        $context->setToken($token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));
        $token
            ->expects($this->once())
            ->method('isAuthenticated')
            ->will($this->returnValue(true))
        ;
        $this->assertFalse($context->isGranted('ROLE_FOO'));

        $manager = $this->getMock('Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface');
        $manager->expects($this->once())->method('decide')->will($this->returnValue(true));
        $context = new Symfony_Component_Security_Core_SecurityContext($this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface'), $manager);
        $context->setToken($token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));
        $token
            ->expects($this->once())
            ->method('isAuthenticated')
            ->will($this->returnValue(true))
        ;
        $this->assertTrue($context->isGranted('ROLE_FOO'));
    }

    public function testGetSetToken()
    {
        $context = new Symfony_Component_Security_Core_SecurityContext(
            $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface'),
            $this->getMock('Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface')
        );
        $this->assertNull($context->getToken());

        $context->setToken($token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));
        $this->assertSame($token, $context->getToken());
    }
}
