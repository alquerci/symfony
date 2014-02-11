<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_EventListener_LocaleListenerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }
    }

    public function testDefaultLocaleWithoutSession()
    {
        $listener = new Symfony_Component_HttpKernel_EventListener_LocaleListener('fr');
        $event = $this->getEvent($request = Symfony_Component_HttpFoundation_Request::create('/'));

        $listener->onKernelRequest($event);
        $this->assertEquals('fr', $request->getLocale());
    }

    public function testLocaleFromRequestAttribute()
    {
        $request = Symfony_Component_HttpFoundation_Request::create('/');
        session_name('foo');
        $request->cookies->set('foo', 'value');

        $request->attributes->set('_locale', 'es');
        $listener = new Symfony_Component_HttpKernel_EventListener_LocaleListener('fr');
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        $this->assertEquals('es', $request->getLocale());
    }

    public function testLocaleSetForRoutingContext()
    {
        if (!class_exists('Symfony_Component_Routing_Router')) {
            $this->markTestSkipped('The "Routing" component is not available');
        }

        // the request context is updated
        $context = $this->getMock('Symfony_Component_Routing_RequestContext');
        $context->expects($this->once())->method('setParameter')->with('_locale', 'es');

        $router = $this->getMock('Symfony_Component_Routing_Router', array('getContext'), array(), '', false);
        $router->expects($this->once())->method('getContext')->will($this->returnValue($context));

        $request = Symfony_Component_HttpFoundation_Request::create('/');

        $request->attributes->set('_locale', 'es');
        $listener = new Symfony_Component_HttpKernel_EventListener_LocaleListener('fr', $router);
        $listener->onKernelRequest($this->getEvent($request));
    }

    private function getEvent(Symfony_Component_HttpFoundation_Request $request)
    {
        return new Symfony_Component_HttpKernel_Event_GetResponseEvent($this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface'), $request, Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST);
    }
}
