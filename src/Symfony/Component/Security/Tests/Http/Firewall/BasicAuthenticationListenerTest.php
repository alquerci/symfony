<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_Firewall_BasicAuthenticationListenerTest extends PHPUnit_Framework_TestCase
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

    public function testHandleWithValidUsernameAndPasswordServerParameters()
    {
        $request = new Symfony_Component_HttpFoundation_Request(array(), array(), array(), array(), array(), array(
            'PHP_AUTH_USER' => 'TheUsername',
            'PHP_AUTH_PW'   => 'ThePassword'
        ));

        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');

        $context = $this->getMock('Symfony_Component_Security_Core_SecurityContextInterface');
        $context
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue(null))
        ;
        $context
            ->expects($this->once())
            ->method('setToken')
            ->with($this->equalTo($token))
        ;

        $authenticationManager = $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface');
        $authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf('Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken'))
            ->will($this->returnValue($token))
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_BasicAuthenticationListener(
            $context,
            $authenticationManager,
            'TheProviderKey',
            $this->getMock('Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface')
        );

        $event = $this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;

        $listener->handle($event);
    }

    public function testHandleWhenAuthenticationFails()
    {
        $request = new Symfony_Component_HttpFoundation_Request(array(), array(), array(), array(), array(), array(
            'PHP_AUTH_USER' => 'TheUsername',
            'PHP_AUTH_PW'   => 'ThePassword'
        ));

        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');

        $context = $this->getMock('Symfony_Component_Security_Core_SecurityContextInterface');
        $context
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue(null))
        ;
        $context
            ->expects($this->once())
            ->method('setToken')
            ->with($this->equalTo(null))
        ;

        $response = new Symfony_Component_HttpFoundation_Response();

        $authenticationEntryPoint = $this->getMock('Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface');
        $authenticationEntryPoint
            ->expects($this->any())
            ->method('start')
            ->with($this->equalTo($request), $this->isInstanceOf('Symfony_Component_Security_Core_Exception_AuthenticationException'))
            ->will($this->returnValue($response))
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_BasicAuthenticationListener(
            $context,
            new Symfony_Component_Security_Core_Authentication_AuthenticationProviderManager(array($this->getMock('Symfony_Component_Security_Core_Authentication_Provider_AuthenticationProviderInterface'))),
            'TheProviderKey',
            $authenticationEntryPoint
        );

        $event = $this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;
        $event
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->equalTo($response))
        ;

        $listener->handle($event);
    }

    public function testHandleWithNoUsernameServerParameter()
    {
        $request = new Symfony_Component_HttpFoundation_Request();

        $context = $this->getMock('Symfony_Component_Security_Core_SecurityContextInterface');
        $context
            ->expects($this->never())
            ->method('getToken')
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_BasicAuthenticationListener(
            $context,
            $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface'),
            'TheProviderKey',
            $this->getMock('Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface')
        );

        $event = $this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;

        $listener->handle($event);
    }

    public function testHandleWithASimilarAuthenticatedToken()
    {
        $request = new Symfony_Component_HttpFoundation_Request(array(), array(), array(), array(), array(), array('PHP_AUTH_USER' => 'TheUsername'));

        $token = new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('TheUsername', 'ThePassword', 'TheProviderKey', array('ROLE_FOO'));

        $context = $this->getMock('Symfony_Component_Security_Core_SecurityContextInterface');
        $context
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token))
        ;

        $authenticationManager = $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface');
        $authenticationManager
            ->expects($this->never())
            ->method('authenticate')
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_BasicAuthenticationListener(
            $context,
            $authenticationManager,
            'TheProviderKey',
            $this->getMock('Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface')
        );

        $event = $this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;

        $listener->handle($event);
    }
}
