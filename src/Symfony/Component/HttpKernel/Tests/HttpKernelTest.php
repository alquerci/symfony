<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_HttpKernelTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    /**
     * @expectedException RuntimeException
     */
    public function testHandleWhenControllerThrowsAnExceptionAndRawIsTrue()
    {
        $kernel = new Symfony_Component_HttpKernel_HttpKernel(new Symfony_Component_EventDispatcher_EventDispatcher(), $this->getResolver(create_function('', 'throw new RuntimeException();')));

        $kernel->handle(new Symfony_Component_HttpFoundation_Request(), Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, true);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testHandleWhenControllerThrowsAnExceptionAndRawIsFalseAndNoListenerIsRegistered()
    {
        $kernel = new Symfony_Component_HttpKernel_HttpKernel(new Symfony_Component_EventDispatcher_EventDispatcher(), $this->getResolver(create_function('', 'throw new RuntimeException();')));

        $kernel->handle(new Symfony_Component_HttpFoundation_Request(), Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, false);
    }

    public function testHandleWhenControllerThrowsAnExceptionAndRawIsFalse()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::EXCEPTION, create_function('$event', '
            $event->setResponse(new Symfony_Component_HttpFoundation_Response($event->getException()->getMessage()));
        '));

        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver(create_function('', 'throw new RuntimeException("foo");')));
        $response = $kernel->handle(new Symfony_Component_HttpFoundation_Request());

        $this->assertEquals('500', $response->getStatusCode());
        $this->assertEquals('foo', $response->getContent());
    }

    public function testHandleExceptionWithARedirectionResponse()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::EXCEPTION, create_function('$event', '
            $event->setResponse(new Symfony_Component_HttpFoundation_RedirectResponse("/login", 301));
        '));

        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver(create_function('', 'throw new Symfony_Component_HttpKernel_Exception_AccessDeniedHttpException();')));
        $response = $kernel->handle(new Symfony_Component_HttpFoundation_Request());

        $this->assertEquals('301', $response->getStatusCode());
        $this->assertEquals('/login', $response->headers->get('Location'));
    }

    public function testHandleHttpException()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::EXCEPTION, create_function('$event', '
            $event->setResponse(new Symfony_Component_HttpFoundation_Response($event->getException()->getMessage()));
        '));

        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver(create_function('', 'throw new Symfony_Component_HttpKernel_Exception_MethodNotAllowedHttpException(array("POST"));')));
        $response = $kernel->handle(new Symfony_Component_HttpFoundation_Request());

        $this->assertEquals('405', $response->getStatusCode());
        $this->assertEquals('POST', $response->headers->get('Allow'));
    }

    /**
     * @dataProvider getStatusCodes
     */
    public function testHandleWhenAnExceptionIsHandledWithASpecificStatusCode($responseStatusCode, $expectedStatusCode)
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::EXCEPTION, create_function('$event', '
            $event->setResponse(new Symfony_Component_HttpFoundation_Response("", '.var_export($responseStatusCode, true).', array("X-Status-Code" => '.var_export($expectedStatusCode, true).')));
        '));

        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver(create_function('', 'throw new RuntimeException();')));
        $response = $kernel->handle(new Symfony_Component_HttpFoundation_Request());

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
        $this->assertFalse($response->headers->has('X-Status-Code'));
    }

    public function getStatusCodes()
    {
        return array(
            array(200, 404),
            array(404, 200),
            array(301, 200),
            array(500, 200),
        );
    }

    public function testHandleWhenAListenerReturnsAResponse()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::REQUEST, create_function('$event', '
            $event->setResponse(new Symfony_Component_HttpFoundation_Response("hello"));
        '));

        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver());

        $this->assertEquals('hello', $kernel->handle(new Symfony_Component_HttpFoundation_Request())->getContent());
    }

    /**
     * @expectedException Symfony_Component_HttpKernel_Exception_NotFoundHttpException
     */
    public function testHandleWhenNoControllerIsFound()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver(false));

        $kernel->handle(new Symfony_Component_HttpFoundation_Request());
    }

    /**
     * @expectedException LogicException
     */
    public function testHandleWhenTheControllerIsNotACallable()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver('foobar'));

        $kernel->handle(new Symfony_Component_HttpFoundation_Request());
    }

    public function testHandleWhenTheControllerIsAClosure()
    {
        $response = new Symfony_Component_HttpFoundation_Response('foo');
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver(array(new Symfony_Component_HttpKernel_Tests_HttpKernelTestClosure1($response), '__invoke')));

        $this->assertSame($response, $kernel->handle(new Symfony_Component_HttpFoundation_Request()));
    }

    public function testHandleWhenTheControllerIsAnObjectWithInvoke()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver(new Symfony_Component_HttpKernel_Tests_Controller()));

        $this->assertResponseEquals(new Symfony_Component_HttpFoundation_Response('foo'), $kernel->handle(new Symfony_Component_HttpFoundation_Request()));
    }

    public function testHandleWhenTheControllerIsAFunction()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver('Symfony_Component_HttpKernel_Tests_controller_func'));

        $this->assertResponseEquals(new Symfony_Component_HttpFoundation_Response('foo'), $kernel->handle(new Symfony_Component_HttpFoundation_Request()));
    }

    public function testHandleWhenTheControllerIsAnArray()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver(array(new Symfony_Component_HttpKernel_Tests_Controller(), 'controller')));

        $this->assertResponseEquals(new Symfony_Component_HttpFoundation_Response('foo'), $kernel->handle(new Symfony_Component_HttpFoundation_Request()));
    }

    public function testHandleWhenTheControllerIsAStaticArray()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver(array('Symfony_Component_HttpKernel_Tests_Controller', 'staticcontroller')));

        $this->assertResponseEquals(new Symfony_Component_HttpFoundation_Response('foo'), $kernel->handle(new Symfony_Component_HttpFoundation_Request()));
    }

    /**
     * @expectedException LogicException
     */
    public function testHandleWhenTheControllerDoesNotReturnAResponse()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver(create_function('', 'return "foo";')));

        $kernel->handle(new Symfony_Component_HttpFoundation_Request());
    }

    public function testHandleWhenTheControllerDoesNotReturnAResponseButAViewIsRegistered()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::VIEW, create_function('$event', '
            $event->setResponse(new Symfony_Component_HttpFoundation_Response($event->getControllerResult()));
        '));
        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver(create_function('', 'return "foo";')));

        $this->assertEquals('foo', $kernel->handle(new Symfony_Component_HttpFoundation_Request())->getContent());
    }

    public function testHandleWithAResponseListener()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, create_function('$event', '
            $event->setResponse(new Symfony_Component_HttpFoundation_Response("foo"));
        '));
        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver());

        $this->assertEquals('foo', $kernel->handle(new Symfony_Component_HttpFoundation_Request())->getContent());
    }

    public function testTerminate()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $kernel = new Symfony_Component_HttpKernel_HttpKernel($dispatcher, $this->getResolver());
        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::TERMINATE, array(new Symfony_Component_HttpKernel_Tests_HttpKernelTestClosure2($called, $capturedKernel, $capturedRequest, $capturedResponse), '__invoke'));

        $kernel->terminate($request = Symfony_Component_HttpFoundation_Request::create('/'), $response = new Symfony_Component_HttpFoundation_Response());
        $this->assertTrue($called);
        $this->assertEquals($kernel, $capturedKernel);
        $this->assertEquals($request, $capturedRequest);
        $this->assertEquals($response, $capturedResponse);
    }

    protected function getResolver($controller = null)
    {
        if (null === $controller) {
            $controller = create_function('', 'return new Symfony_Component_HttpFoundation_Response("Hello");');
        }

        $resolver = $this->getMock('Symfony_Component_HttpKernel_Controller_ControllerResolverInterface');
        $resolver->expects($this->any())
            ->method('getController')
            ->will($this->returnValue($controller));
        $resolver->expects($this->any())
            ->method('getArguments')
            ->will($this->returnValue(array()));

        return $resolver;
    }

    protected function assertResponseEquals(Symfony_Component_HttpFoundation_Response $expected, Symfony_Component_HttpFoundation_Response $actual)
    {
        $expected->setDate($actual->getDate());
        $this->assertEquals($expected, $actual);
    }
}

class Symfony_Component_HttpKernel_Tests_Controller
{
    public function __invoke()
    {
        return new Symfony_Component_HttpFoundation_Response('foo');
    }

    public function controller()
    {
        return new Symfony_Component_HttpFoundation_Response('foo');
    }

    public static function staticController()
    {
        return new Symfony_Component_HttpFoundation_Response('foo');
    }
}

function Symfony_Component_HttpKernel_Tests_controller_func()
{
    return new Symfony_Component_HttpFoundation_Response('foo');
}

class Symfony_Component_HttpKernel_Tests_HttpKernelTestClosure1
{
    private $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function __invoke()
    {
        return $this->response;
    }
}

class Symfony_Component_HttpKernel_Tests_HttpKernelTestClosure2
{
    private $called;
    private $capturedKernel;
    private $capturedRequest;
    private $capturedResponse;

    public function __construct(&$called, &$capturedKernel, &$capturedRequest, &$capturedResponse)
    {
        $this->called = &$called;
        $this->capturedKernel = &$capturedKernel;
        $this->capturedRequest = &$capturedRequest;
        $this->capturedResponse = &$capturedResponse;
    }

    public function __invoke($event)
    {
        $this->called = true;
        $this->capturedKernel = $event->getKernel();
        $this->capturedRequest = $event->getRequest();
        $this->capturedResponse = $event->getResponse();
    }
}
