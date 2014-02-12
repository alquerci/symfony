<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_FirewallTest extends PHPUnit_Framework_TestCase
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

    public function testOnKernelRequestRegistersExceptionListener()
    {
        $dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');

        $listener = $this->getMock('Symfony_Component_Security_Http_Firewall_ExceptionListener', array(), array(), '', false);
        $listener
            ->expects($this->once())
            ->method('register')
            ->with($this->equalTo($dispatcher))
        ;

        $request = $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false);

        $map = $this->getMock('Symfony_Component_Security_Http_FirewallMapInterface');
        $map
            ->expects($this->once())
            ->method('getListeners')
            ->with($this->equalTo($request))
            ->will($this->returnValue(array(array(), $listener)))
        ;

        $event = new Symfony_Component_HttpKernel_Event_GetResponseEvent($this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface'), $request, Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST);

        $firewall = new Symfony_Component_Security_Http_Firewall($map, $dispatcher);
        $firewall->onKernelRequest($event);
    }

    public function testOnKernelRequestStopsWhenThereIsAResponse()
    {
        $response = $this->getMock('Symfony_Component_HttpFoundation_Response');

        $first = $this->getMock('Symfony_Component_Security_Http_Firewall_ListenerInterface');
        $first
            ->expects($this->once())
            ->method('handle')
        ;

        $second = $this->getMock('Symfony_Component_Security_Http_Firewall_ListenerInterface');
        $second
            ->expects($this->never())
            ->method('handle')
        ;

        $map = $this->getMock('Symfony_Component_Security_Http_FirewallMapInterface');
        $map
            ->expects($this->once())
            ->method('getListeners')
            ->will($this->returnValue(array(array($first, $second), null)))
        ;

        $event = $this->getMock(
            'Symfony_Component_HttpKernel_Event_GetResponseEvent',
            array('hasResponse'),
            array(
                $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface'),
                $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false),
                Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST
            )
        );
        $event
            ->expects($this->once())
            ->method('hasResponse')
            ->will($this->returnValue(true))
        ;

        $firewall = new Symfony_Component_Security_Http_Firewall($map, $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface'));
        $firewall->onKernelRequest($event);
    }

    public function testOnKernelRequestWithSubRequest()
    {
        $map = $this->getMock('Symfony_Component_Security_Http_FirewallMapInterface');
        $map
            ->expects($this->never())
            ->method('getListeners')
        ;

        $event = new Symfony_Component_HttpKernel_Event_GetResponseEvent(
            $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface'),
            $this->getMock('Symfony_Component_HttpFoundation_Request'),
            Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST
        );

        $firewall = new Symfony_Component_Security_Http_Firewall($map, $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface'));
        $firewall->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }
}
