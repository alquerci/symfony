<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_EventListener_ResponseListenerTest extends PHPUnit_Framework_TestCase
{
    private $dispatcher;

    private $kernel;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        $this->dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $listener = new Symfony_Component_HttpKernel_EventListener_ResponseListener('UTF-8');
        $this->dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, array($listener, 'onKernelResponse'));

        $this->kernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');

    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->kernel = null;
    }

    public function testFilterDoesNothingForSubRequests()
    {
        $response = new Symfony_Component_HttpFoundation_Response('foo');

        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent($this->kernel, new Symfony_Component_HttpFoundation_Request(), Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST, $response);
        $this->dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, $event);

        $this->assertEquals('', $event->getResponse()->headers->get('content-type'));
    }

    public function testFilterSetsNonDefaultCharsetIfNotOverridden()
    {
        $listener = new Symfony_Component_HttpKernel_EventListener_ResponseListener('ISO-8859-15');
        $this->dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, array($listener, 'onKernelResponse'), 1);

        $response = new Symfony_Component_HttpFoundation_Response('foo');

        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent($this->kernel, Symfony_Component_HttpFoundation_Request::create('/'), Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, $response);
        $this->dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, $event);

        $this->assertEquals('ISO-8859-15', $response->getCharset());
    }

    public function testFilterDoesNothingIfCharsetIsOverridden()
    {
        $listener = new Symfony_Component_HttpKernel_EventListener_ResponseListener('ISO-8859-15');
        $this->dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, array($listener, 'onKernelResponse'), 1);

        $response = new Symfony_Component_HttpFoundation_Response('foo');
        $response->setCharset('ISO-8859-1');

        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent($this->kernel, Symfony_Component_HttpFoundation_Request::create('/'), Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, $response);
        $this->dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, $event);

        $this->assertEquals('ISO-8859-1', $response->getCharset());
    }

    public function testFiltersSetsNonDefaultCharsetIfNotOverriddenOnNonTextContentType()
    {
        $listener = new Symfony_Component_HttpKernel_EventListener_ResponseListener('ISO-8859-15');
        $this->dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, array($listener, 'onKernelResponse'), 1);

        $response = new Symfony_Component_HttpFoundation_Response('foo');
        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->setRequestFormat('application/json');

        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent($this->kernel, $request, Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, $response);
        $this->dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, $event);

        $this->assertEquals('ISO-8859-15', $response->getCharset());
    }
}
