<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_EventListener_EsiListenerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }
    }

    public function testFilterDoesNothingForSubRequests()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $response = new Symfony_Component_HttpFoundation_Response('foo <esi:include src="" />');
        $listener = new Symfony_Component_HttpKernel_EventListener_EsiListener(new Symfony_Component_HttpKernel_HttpCache_Esi());

        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, array($listener, 'onKernelResponse'));
        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent($kernel, new Symfony_Component_HttpFoundation_Request(), Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST, $response);
        $dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, $event);

        $this->assertEquals('', $event->getResponse()->headers->get('Surrogate-Control'));
    }

    public function testFilterWhenThereIsSomeEsiIncludes()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $response = new Symfony_Component_HttpFoundation_Response('foo <esi:include src="" />');
        $listener = new Symfony_Component_HttpKernel_EventListener_EsiListener(new Symfony_Component_HttpKernel_HttpCache_Esi());

        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, array($listener, 'onKernelResponse'));
        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent($kernel, new Symfony_Component_HttpFoundation_Request(), Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, $response);
        $dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, $event);

        $this->assertEquals('content="ESI/1.0"', $event->getResponse()->headers->get('Surrogate-Control'));
    }

    public function testFilterWhenThereIsNoEsiIncludes()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $response = new Symfony_Component_HttpFoundation_Response('foo');
        $listener = new Symfony_Component_HttpKernel_EventListener_EsiListener(new Symfony_Component_HttpKernel_HttpCache_Esi());

        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, array($listener, 'onKernelResponse'));
        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent($kernel, new Symfony_Component_HttpFoundation_Request(), Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, $response);
        $dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, $event);

        $this->assertEquals('', $event->getResponse()->headers->get('Surrogate-Control'));
    }
}
