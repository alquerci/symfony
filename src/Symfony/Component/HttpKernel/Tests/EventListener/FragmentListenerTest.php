<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_EventListener_FragmentListenerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }
    }

    public function testOnlyTriggeredOnFragmentRoute()
    {
        $request = Symfony_Component_HttpFoundation_Request::create('http://example.com/foo?_path=foo%3Dbar%26_controller%3Dfoo');

        $listener = new Symfony_Component_HttpKernel_EventListener_FragmentListener(new Symfony_Component_HttpKernel_UriSigner('foo'));
        $event = $this->createGetResponseEvent($request);

        $expected = $request->attributes->all();

        $listener->onKernelRequest($event);

        $this->assertEquals($expected, $request->attributes->all());
        $this->assertTrue($request->query->has('_path'));
    }

    /**
     * @expectedException Symfony_Component_HttpKernel_Exception_AccessDeniedHttpException
     */
    public function testAccessDeniedWithNonSafeMethods()
    {
        $request = Symfony_Component_HttpFoundation_Request::create('http://example.com/_fragment', 'POST');

        $listener = new Symfony_Component_HttpKernel_EventListener_FragmentListener(new Symfony_Component_HttpKernel_UriSigner('foo'));
        $event = $this->createGetResponseEvent($request);

        $listener->onKernelRequest($event);
    }

    /**
     * @expectedException Symfony_Component_HttpKernel_Exception_AccessDeniedHttpException
     */
    public function testAccessDeniedWithNonLocalIps()
    {
        $request = Symfony_Component_HttpFoundation_Request::create('http://example.com/_fragment', 'GET', array(), array(), array(), array('REMOTE_ADDR' => '10.0.0.1'));

        $listener = new Symfony_Component_HttpKernel_EventListener_FragmentListener(new Symfony_Component_HttpKernel_UriSigner('foo'));
        $event = $this->createGetResponseEvent($request);

        $listener->onKernelRequest($event);
    }

    /**
     * @expectedException Symfony_Component_HttpKernel_Exception_AccessDeniedHttpException
     */
    public function testAccessDeniedWithWrongSignature()
    {
        $request = Symfony_Component_HttpFoundation_Request::create('http://example.com/_fragment', 'GET', array(), array(), array(), array('REMOTE_ADDR' => '10.0.0.1'));

        $listener = new Symfony_Component_HttpKernel_EventListener_FragmentListener(new Symfony_Component_HttpKernel_UriSigner('foo'));
        $event = $this->createGetResponseEvent($request);

        $listener->onKernelRequest($event);
    }

    public function testWithSignature()
    {
        $signer = new Symfony_Component_HttpKernel_UriSigner('foo');
        $request = Symfony_Component_HttpFoundation_Request::create($signer->sign('http://example.com/_fragment?_path=foo%3Dbar%26_controller%3Dfoo'), 'GET', array(), array(), array(), array('REMOTE_ADDR' => '10.0.0.1'));

        $listener = new Symfony_Component_HttpKernel_EventListener_FragmentListener($signer);
        $event = $this->createGetResponseEvent($request);

        $listener->onKernelRequest($event);

        $this->assertEquals(array('foo' => 'bar', '_controller' => 'foo'), $request->attributes->get('_route_params'));
        $this->assertFalse($request->query->has('_path'));
    }

    private function createGetResponseEvent(Symfony_Component_HttpFoundation_Request $request)
    {
        return new Symfony_Component_HttpKernel_Event_GetResponseEvent($this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface'), $request, Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST);
    }
}
