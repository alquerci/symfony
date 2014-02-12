<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_Firewall_AccessListenerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        if (!class_exists('Symfony_Component_HttpKernel_HttpKernel')) {
            $this->markTestSkipped('The "HttpKernel" component is not available');
        }
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_AccessDeniedException
     */
    public function testHandleWhenTheAccessDecisionManagerDecidesToRefuseAccess()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false);

        $accessMap = $this->getMock('Symfony_Component_Security_Http_AccessMapInterface');
        $accessMap
            ->expects($this->any())
            ->method('getPatterns')
            ->with($this->equalTo($request))
            ->will($this->returnValue(array(array('foo' => 'bar'), null)))
        ;

        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $token
            ->expects($this->any())
            ->method('isAuthenticated')
            ->will($this->returnValue(true))
        ;

        $context = $this->getMock('Symfony_Component_Security_Core_SecurityContextInterface');
        $context
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token))
        ;

        $accessDecisionManager = $this->getMock('Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface');
        $accessDecisionManager
            ->expects($this->once())
            ->method('decide')
            ->with($this->equalTo($token), $this->equalTo(array('foo' => 'bar')), $this->equalTo($request))
            ->will($this->returnValue(false))
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_AccessListener(
            $context,
            $accessDecisionManager,
            $accessMap,
            $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface')
        );

        $event = $this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;

        $listener->handle($event);
    }

    public function testHandleWhenTheTokenIsNotAuthenticated()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false);

        $accessMap = $this->getMock('Symfony_Component_Security_Http_AccessMapInterface');
        $accessMap
            ->expects($this->any())
            ->method('getPatterns')
            ->with($this->equalTo($request))
            ->will($this->returnValue(array(array('foo' => 'bar'), null)))
        ;

        $notAuthenticatedToken = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $notAuthenticatedToken
            ->expects($this->any())
            ->method('isAuthenticated')
            ->will($this->returnValue(false))
        ;

        $authenticatedToken = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $authenticatedToken
            ->expects($this->any())
            ->method('isAuthenticated')
            ->will($this->returnValue(true))
        ;

        $authManager = $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface');
        $authManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->equalTo($notAuthenticatedToken))
            ->will($this->returnValue($authenticatedToken))
        ;

        $context = $this->getMock('Symfony_Component_Security_Core_SecurityContextInterface');
        $context
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($notAuthenticatedToken))
        ;
        $context
            ->expects($this->once())
            ->method('setToken')
            ->with($this->equalTo($authenticatedToken))
        ;

        $accessDecisionManager = $this->getMock('Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface');
        $accessDecisionManager
            ->expects($this->once())
            ->method('decide')
            ->with($this->equalTo($authenticatedToken), $this->equalTo(array('foo' => 'bar')), $this->equalTo($request))
            ->will($this->returnValue(true))
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_AccessListener(
            $context,
            $accessDecisionManager,
            $accessMap,
            $authManager
        );

        $event = $this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;

        $listener->handle($event);
    }

    public function testHandleWhenThereIsNoAccessMapEntryMatchingTheRequest()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false);

        $accessMap = $this->getMock('Symfony_Component_Security_Http_AccessMapInterface');
        $accessMap
            ->expects($this->any())
            ->method('getPatterns')
            ->with($this->equalTo($request))
            ->will($this->returnValue(array(null, null)))
        ;

        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $token
            ->expects($this->never())
            ->method('isAuthenticated')
        ;

        $context = $this->getMock('Symfony_Component_Security_Core_SecurityContextInterface');
        $context
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token))
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_AccessListener(
            $context,
            $this->getMock('Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface'),
            $accessMap,
            $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface')
        );

        $event = $this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;

        $listener->handle($event);
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_AuthenticationCredentialsNotFoundException
     */
    public function testHandleWhenTheSecurityContextHasNoToken()
    {
        $context = $this->getMock('Symfony_Component_Security_Core_SecurityContextInterface');
        $context
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue(null))
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_AccessListener(
            $context,
            $this->getMock('Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface'),
            $this->getMock('Symfony_Component_Security_Http_AccessMapInterface'),
            $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface')
        );

        $event = $this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false);

        $listener->handle($event);
    }
}
