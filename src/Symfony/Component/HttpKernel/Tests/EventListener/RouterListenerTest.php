<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_EventListener_RouterListenerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        if (!class_exists('Symfony_Component_Routing_Router')) {
            $this->markTestSkipped('The "Routing" component is not available');
        }
    }

    /**
     * @dataProvider getPortData
     */
    public function testPort($defaultHttpPort, $defaultHttpsPort, $uri, $expectedHttpPort, $expectedHttpsPort)
    {
        $urlMatcher = $this->getMockBuilder('Symfony_Component_Routing_Matcher_UrlMatcherInterface')
                             ->disableOriginalConstructor()
                             ->getMock();
        $context = new Symfony_Component_Routing_RequestContext();
        $context->setHttpPort($defaultHttpPort);
        $context->setHttpsPort($defaultHttpsPort);
        $urlMatcher->expects($this->any())
                     ->method('getContext')
                     ->will($this->returnValue($context));

        $listener = new Symfony_Component_HttpKernel_EventListener_RouterListener($urlMatcher);
        $event = $this->createGetResponseEventForUri($uri);
        $listener->onKernelRequest($event);

        $this->assertEquals($expectedHttpPort, $context->getHttpPort());
        $this->assertEquals($expectedHttpsPort, $context->getHttpsPort());
        $this->assertEquals(0 === strpos($uri, 'https') ? 'https' : 'http', $context->getScheme());
    }

    public function getPortData()
    {
        return array(
            array(80, 443, 'http://localhost/', 80, 443),
            array(80, 443, 'http://localhost:90/', 90, 443),
            array(80, 443, 'https://localhost/', 80, 443),
            array(80, 443, 'https://localhost:90/', 80, 90),
        );
    }

    /**
     * @param string $uri
     *
     * @return Symfony_Component_HttpKernel_Event_GetResponseEvent
     */
    private function createGetResponseEventForUri($uri)
    {
        $kernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $request = Symfony_Component_HttpFoundation_Request::create($uri);
        $request->attributes->set('_controller', null); // Prevents going in to routing process

        return new Symfony_Component_HttpKernel_Event_GetResponseEvent($kernel, $request, Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMatcher()
    {
        new Symfony_Component_HttpKernel_EventListener_RouterListener(new stdClass());
    }

    public function testRequestMatcher()
    {
        $kernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $request = Symfony_Component_HttpFoundation_Request::create('http://localhost/');
        $event = new Symfony_Component_HttpKernel_Event_GetResponseEvent($kernel, $request, Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST);

        $requestMatcher = $this->getMock('Symfony_Component_Routing_Matcher_RequestMatcherInterface');
        $requestMatcher->expects($this->once())
                       ->method('matchRequest')
                       ->with($this->isInstanceOf('Symfony_Component_HttpFoundation_Request'))
                       ->will($this->returnValue(array()));

        $listener = new Symfony_Component_HttpKernel_EventListener_RouterListener($requestMatcher, new Symfony_Component_Routing_RequestContext());
        $listener->onKernelRequest($event);
    }

    public function testSubRequestWithDifferentMethod()
    {
        $kernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $request = Symfony_Component_HttpFoundation_Request::create('http://localhost/', 'post');
        $event = new Symfony_Component_HttpKernel_Event_GetResponseEvent($kernel, $request, Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST);

        $requestMatcher = $this->getMock('Symfony_Component_Routing_Matcher_RequestMatcherInterface');
        $requestMatcher->expects($this->any())
                       ->method('matchRequest')
                       ->with($this->isInstanceOf('Symfony_Component_HttpFoundation_Request'))
                       ->will($this->returnValue(array()));

        $context = new Symfony_Component_Routing_RequestContext();
        $requestMatcher->expects($this->any())
                       ->method('getContext')
                       ->will($this->returnValue($context));

        $listener = new Symfony_Component_HttpKernel_EventListener_RouterListener($requestMatcher, new Symfony_Component_Routing_RequestContext());
        $listener->onKernelRequest($event);

        // sub-request with another HTTP method
        $kernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $request = Symfony_Component_HttpFoundation_Request::create('http://localhost/', 'get');
        $event = new Symfony_Component_HttpKernel_Event_GetResponseEvent($kernel, $request, Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST);

        $listener->onKernelRequest($event);

        $this->assertEquals('GET', $context->getMethod());
    }
}
