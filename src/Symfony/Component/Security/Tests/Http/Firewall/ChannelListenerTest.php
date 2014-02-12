<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_Firewall_ChannelListenerTest extends PHPUnit_Framework_TestCase
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

    public function testHandleWithNotSecuredRequestAndHttpChannel()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false);
        $request
            ->expects($this->any())
            ->method('isSecure')
            ->will($this->returnValue(false))
        ;

        $accessMap = $this->getMock('Symfony_Component_Security_Http_AccessMapInterface');
        $accessMap
            ->expects($this->any())
            ->method('getPatterns')
            ->with($this->equalTo($request))
            ->will($this->returnValue(array(array(), 'http')))
        ;

        $entryPoint = $this->getMock('Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface');
        $entryPoint
            ->expects($this->never())
            ->method('start')
        ;

        $event = $this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;
        $event
            ->expects($this->never())
            ->method('setResponse')
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_ChannelListener($accessMap, $entryPoint);
        $listener->handle($event);
    }

    public function testHandleWithSecuredRequestAndHttpsChannel()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false);
        $request
            ->expects($this->any())
            ->method('isSecure')
            ->will($this->returnValue(true))
        ;

        $accessMap = $this->getMock('Symfony_Component_Security_Http_AccessMapInterface');
        $accessMap
            ->expects($this->any())
            ->method('getPatterns')
            ->with($this->equalTo($request))
            ->will($this->returnValue(array(array(), 'https')))
        ;

        $entryPoint = $this->getMock('Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface');
        $entryPoint
            ->expects($this->never())
            ->method('start')
        ;

        $event = $this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;
        $event
            ->expects($this->never())
            ->method('setResponse')
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_ChannelListener($accessMap, $entryPoint);
        $listener->handle($event);
    }

    public function testHandleWithNotSecuredRequestAndHttpsChannel()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false);
        $request
            ->expects($this->any())
            ->method('isSecure')
            ->will($this->returnValue(false))
        ;

        $response = new Symfony_Component_HttpFoundation_Response();

        $accessMap = $this->getMock('Symfony_Component_Security_Http_AccessMapInterface');
        $accessMap
            ->expects($this->any())
            ->method('getPatterns')
            ->with($this->equalTo($request))
            ->will($this->returnValue(array(array(), 'https')))
        ;

        $entryPoint = $this->getMock('Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface');
        $entryPoint
            ->expects($this->once())
            ->method('start')
            ->with($this->equalTo($request))
            ->will($this->returnValue($response))
        ;

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

        $listener = new Symfony_Component_Security_Http_Firewall_ChannelListener($accessMap, $entryPoint);
        $listener->handle($event);
    }

    public function testHandleWithSecuredRequestAndHttpChannel()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false);
        $request
            ->expects($this->any())
            ->method('isSecure')
            ->will($this->returnValue(true))
        ;

        $response = new Symfony_Component_HttpFoundation_Response();

        $accessMap = $this->getMock('Symfony_Component_Security_Http_AccessMapInterface');
        $accessMap
            ->expects($this->any())
            ->method('getPatterns')
            ->with($this->equalTo($request))
            ->will($this->returnValue(array(array(), 'http')))
        ;

        $entryPoint = $this->getMock('Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface');
        $entryPoint
            ->expects($this->once())
            ->method('start')
            ->with($this->equalTo($request))
            ->will($this->returnValue($response))
        ;

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

        $listener = new Symfony_Component_Security_Http_Firewall_ChannelListener($accessMap, $entryPoint);
        $listener->handle($event);
    }
}
